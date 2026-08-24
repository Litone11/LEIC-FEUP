<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Invitation;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\ProjectInvitationMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class ProjectController extends Controller
{
    // ============================================================
    // LIST PROJECTS 
    // ============================================================
    public function index(Request $request)
    {
        $user   = $this->user();
        $userId = $this->userId();

        $this->authorize('viewAny', Project::class);

        $sort   = $request->input('sort', 'newest');
        $search = $request->input('search', '');
        $archivedView = $sort === 'archived';

        $projectsQuery = Project::query()
            ->forUser($userId)
            ->withTaskStats()
            ->withMemberCount()
            ->withUserPivotFor($userId);

        if ($search !== '') {
            $projectsQuery->where(function ($q) use ($search) {
                $q->where('project.name', 'ILIKE', "%{$search}%")
                    ->orWhere('project.description', 'ILIKE', "%{$search}%");
            });
        }

        
    if ($sort === 'favorites') {
            $projectsQuery
                ->addSelect([
                    'is_favorite' => Project::select('is_favorite')
                        ->from('related_to')
                        ->whereColumn('related_to.project_id', 'project.project_id')
                        ->where('related_to.user_id', $userId)
                        ->limit(1)
                ])
                ->orderByDesc('is_favorite')
                ->orderByDesc('created_at');
        }
    elseif ($sort === 'archived') {
            $projectsQuery
                ->orderBy('project.is_archived', 'desc')
                ->orderBy('project.created_at', 'desc')
                ->orderBy('project.project_id', 'desc');
        } elseif ($sort === 'oldest') {
            $projectsQuery
                ->orderBy('project.created_at', 'asc')
                ->orderBy('project.project_id', 'asc');
        } else {
            $projectsQuery
                ->orderBy('project.created_at', 'desc')
                ->orderBy('project.project_id', 'desc');
        }

        $projects = $projectsQuery->get()->map(fn ($p) => [
            'id'          => $p->project_id,
            'name'        => $p->name,
            'description' => $p->description,
            'progress'    => $p->progress,
            'date'        => $p->formatted_date,
            'members'     => $p->members,
            'is_favorite' => $p->users->first()?->pivot?->is_favorite ?? false,
            'is_coordinator' => $p->isCoordinatorFor($this->userId()),
            'is_archived' => (bool) $p->is_archived,

        ]);

        if ($request->ajax()) {
            return $projects->map(fn ($project) =>
                view('partials.projects.project_card', ['project' => $project])->render()
            )->implode('');
        }

        return view('pages.projects_list', [
            'user'     => $user,
            'projects' => $projects,
            'archivedView' => $archivedView,
            'filters'  => [
                'sort'   => $sort,
                'search' => $search,
            ],
        ]);
    }



    // ============================================================
    // INDIVIDUAL PROJECT PAGE
    // ============================================================
    public function show(Project $project)
    {
        $user   = $this->user();
        $userId = $this->userId();
//redundante
        $this->ensureProjectMembership($project);
        $this->authorize('view',$project);

        $userRole = $project->roleOf($userId);

        $allTasks = $project->tasks()
            ->with(['responsible', 'assignee'])
            ->latest()
            ->get()
            ->map(fn($t) => $t->toArrayForProject($user));

        return view('pages.individual_project', [
            'user'             => $user,
            'project'          => $project,
            'allTasks'         => $allTasks,
            'myTasks'          => $userRole !== 'coordinator'
                                    ? $allTasks->where('assignee_id', $userId)
                                    : collect(),
            'responsibleTasks' => $userRole !== 'coordinator'
                                    ? $allTasks->where('task_responsible_id', $userId)
                                    : collect(),
            'members'          => $project->topMembers(),
            'summary'          => $project->computeSummary(),
            'userRole'         => $userRole,
        ]);
    }


    public function members(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('view',$project);

        $user = $this->user();
        $summary = $project->computeSummary();
        $search = trim((string) $request->query('search', ''));

        $members = $project->sortedMembers();
        if ($search !== '') {
            $members = $members->filter(function ($member) use ($search) {
                $name  = strtolower($member->username ?? '');
                $email = strtolower($member->email ?? '');
                $term  = strtolower($search);
                return str_contains($name, $term) || str_contains($email, $term);
            })->values();
        }

        $userRole = $project->roleOf($this->userId());

        return view('pages.project_members', [
            'user'      => $user,
            'project'   => $project,
            'members'   => $members,
            'summary'   => $summary,
            'userRole'  => $userRole,
            'search'    => $search,
        ]);
    }

    public function notifyInvitationReceived(Project $project, User $receiver, ?string $senderName = null): Notification
    {
        $payload = json_encode([
            't'   => 'invite',
            's'   => Str::limit($senderName ?? $this->user()->username, 80, ''),
            'pn'  => Str::limit($project->name, 80, ''),
            'pid' => $project->project_id,
        ]);

        $notification = Notification::create([
            'title'       => 'Convite para projeto',
            'message_'    => $payload,
            'created_at'  => now(),
            'receiver_id' => $receiver->user_id,
            'is_read'     => false,
        ]);

        event(new NotificationEvent($notification));

        return $notification;
    }



    public function removeMember(Project $project, User $user)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('update',$project);//Self remove
        $currentUserId = $this->userId();
        $isSelfRemoval = $user->user_id === $this->userId();
        $role = $project->roleOf($user->user_id);
        $memberCount = $project->users()->count();

        // Coordenador só pode sair se for o único membro
        if ($role === 'coordinator' && $isSelfRemoval && $memberCount > 1) {
            return redirect()->back()
                ->with('error', 'Não podes sair do projeto porque és coordenador e há outros membros.');
        }
        // -----------------------------------------------------
        // UNASSIGN tasks onde a pessoa era responsável / assignee
        // -----------------------------------------------------
        $project->tasks()
            ->where(function ($q) use ($user) {
                $q->where('task_responsible_id', $user->user_id)
                ->orWhere('assignee_id', $user->user_id);
            })
            ->get()
            ->each(function ($task) {
                $task->task_responsible_id = null;
                $task->assignee_id = null;
                $task->save();
            });

        // -----------------------------------------------------
        // REMOVER membro do projeto
        // -----------------------------------------------------
        $project->users()->detach($user->user_id);
        if($user->user_id == $currentUserId )return redirect()->route('dashboard')->with('success', 'Saiste com sucesso');
        return redirect() 
            ->route('projects.members', $project)
            ->with('success', 'Membro removido e tarefas foram desatribuídas.');
    }

    public function inviteMember(Request $request, Project $project)
    {
        $this->authorize('coordinatorPermission', $project);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('error', 'Utilizador não encontrado ou sem conta ativa.');
        }

        if ($project->hasMember($user->user_id)) {
            return back()->with('error', 'Este utilizador já faz parte do projeto.');
        }

        $invitation = Invitation::create([
            'sender_id'   => $this->userId(),
            'receiver_id' => $user->user_id,
            'project_id'  => $project->project_id,
            'is_accepted' => false,
        ]);

        $this->notifyInvitationReceived($project, $user);

        $mailData = [
            'receiver_name' => $user->username,
            'project_name'  => $project->name,
            'accept_url'    => route('invitations.accept', $invitation),
        ];

        Mail::to($user->email)->send(new ProjectInvitationMail($mailData));

        return back()->with('success', 'Convite enviado com sucesso!');
    }

    public function addMembers(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission',$project);

        // Lista de emails (string JSON) vindo do formulário
        $emails = json_decode($request->members, true) ?? [];

        foreach ($emails as $email) {
            // Se já existe utilizador com este email
            $user = User::where('email', $email)->first();
            if (!$user) {
                continue;
            }

            // $user->makeRegularUser();

            if ($project->hasMember($user->user_id)) {
                continue;
            }

            // Create invitation
            $invitation = Invitation::create([
                'sender_id' => $this->userId(),
                'receiver_id' => $user->user_id,
                'project_id' => $project->project_id,
                'is_accepted' => false,
            ]);

            $this->notifyInvitationReceived($project, $user);

/*             $mailData = [
                'receiver_name' => $user->username,
                'project_name'  => $project->name,
                'accept_url'    => route('invitations.accept', $invitation),
            ];

            Mail::to($user->email)->send(new ProjectInvitationMail($mailData)); */
        }

        return back()->with('success', 'Todos os pedidos foram enviados!');
    }

    public function promoteMember(Project $project, User $user)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('updateMembers',$project);

        $currentUserId = $this->userId();

        // Only coordinators can promote
        $this->authorize('coordinatorPermission',$project);
/*         if ($project->roleOf($currentUserId) !== 'coordinator') {
            abort(403);
        } */

        // Cannot promote yourself
        if ($user->user_id == $currentUserId) {
            return back()->with('error', 'Não podes promover-te.');
        }

        // Demote current coordinator to normal
        $project->users()->updateExistingPivot($currentUserId, [
            'user_role' => 'normal'
        ]);

        // Promote target user
        $project->users()->updateExistingPivot($user->user_id, [
            'user_role' => 'coordinator'
        ]);
        

        $project->notifyCoordinatorChange();

        return redirect()
            ->route('projects.members', $project)
            ->with('success', "{$user->username} agora é o coordenador do projeto.");
    }

    // ============================================================
    // RESPONDER A CONVITES (UTILIZADOR JÁ EXISTENTE)
    // ============================================================
    public function acceptInvite(Request $request)
    {
        // Link tem de ser assinado e válido
        abort_unless($request->hasValidSignature(), 401);
        $user = $this->user();

        // Segurança: o link tem o id do utilizador destino
        if ((int) $request->query('u') !== $user->user_id) {
            abort(403);
        }

        // Projeto a que se refere o convite
        $project = Project::findOrFail((int) $request->query('p'));

        // Se ainda não é membro, adiciona como normal
        if (!$project->hasMember($user->user_id)) {
            $project->users()->syncWithoutDetaching([
                $user->user_id => [
                    'user_role'  => 'normal',
                    'is_favorite'=> false,
                ]
            ]);
        }

        // Marca convite como aceite e notifica coordenador (reutiliza proc_tran03)
        $invitation = Invitation::where('project_id', $project->project_id)
            ->where('receiver_id', $user->user_id)
            ->latest('invitation_id')
            ->first();

        if ($invitation) {
            try {
                DB::transaction(function () use ($invitation) {
                    DB::statement('CALL proc_tran03(?)', [$invitation->invitation_id]);
                });

                if ($notification = Notification::latest('notification_id')->first()) {
                    event(new NotificationEvent($notification));
                }
            } catch (\Throwable $e) {
                Log::warning('proc_tran03 failed on signed accept', [
                    'invitation_id' => $invitation->invitation_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($notificationId = $request->query('n')) {
            Notification::where('notification_id', $notificationId)
                ->where('receiver_id', $user->user_id)
                ->update(['is_read' => true]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Convite aceite. Já fazes parte do projeto.');
    }

    public function rejectInvite(Request $request)
    {
        // Link tem de ser assinado e válido
        abort_unless($request->hasValidSignature(), 401);
        $user = $this->user();

        // Segurança: confirma se o link corresponde ao utilizador autenticado
        if ((int) $request->query('u') !== $user->user_id) {
            abort(403);
        }

        // Projeto a que se refere o convite
        $project = Project::findOrFail((int) $request->query('p'));

        // Marca notificação como lida, se existir
        if ($notificationId = $request->query('n')) {
            Notification::where('notification_id', $notificationId)
                ->where('receiver_id', $user->user_id)
                ->update(['is_read' => true]);
        }

        return redirect()
            ->route('notifications')
            ->with('success', 'Convite rejeitado.');
    }

    // ============================================================
    // CREATE PROJECT
    // ============================================================
    public function store(Request $request)
    {
        $user   = $this->user();
        $userId = $this->userId();
        $this->authorize('create',Project::class);
       //$user->makeRegularUser();
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:256'],
            'members'     => ['nullable', 'string'],
        ]);

        $project = Project::create([
            'name'        => $validated['name'],
            'description' => $validated['description'],
            'is_archived' => false,
        ]);

        $project->users()->attach($userId, [
            'user_role'   => 'coordinator',
            'is_favorite' => false,
        ]);

        // Add members
        $members = $this->jsonArray($validated['members'] ?? '[]');

        foreach ($members as $email) {
            $member = User::where('email', $email)->first();
            if (!$member) continue;
            //$member->makeRegularUser();
/*             $project->users()->syncWithoutDetaching([
                $member->user_id => [
                    'user_role'   => 'normal',
                    'is_favorite' => false,
                ],
            ]); */
            $invitation = Invitation::create([
                'sender_id' => $this->userId(),
                'receiver_id' => $member->user_id,
                'project_id' => $project->project_id,
                'is_accepted' => false,
            ]);

            $this->notifyInvitationReceived($project, $member);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Projeto criado com sucesso.');
    }

    public function archive(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission', $project);

        $project->update(['is_archived' => true]);

        $message = 'Projeto arquivado.';
        return $request->expectsJson()
            ? response()->json(['success' => true, 'is_archived' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    public function unarchive(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission', $project);

        $project->update(['is_archived' => false]);

        $message = 'Projeto desarquivado.';
        return $request->expectsJson()
            ? response()->json(['success' => true, 'is_archived' => false, 'message' => $message])
            : back()->with('success', $message);
    }

    public function settings(Project $project)
    {
        $this->ensureProjectMembership($project);

        $user = $this->user();
        $userRole = $project->roleOf($this->userId());
        
        $this->authorize('coordinatorPermission',$project);//IDK
/*         if ($userRole !== 'coordinator') {
            abort(403);
        } */
        return view('pages.edit_project', [
            'user' => $user,
            'project' => $project,
            'members' => $project->topMembers(),
            'userRole' => $userRole,
            'summary' => $project->computeSummary(),
        ]);
    }
    public function updateSettings(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission',$project);
     
/*         if ($project->roleOf($this->userId()) !== 'coordinator') {
            abort(403);
        } */

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:256'],
            'color'       => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],

        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.settings', $project)
            ->with('success', 'Configurações do projeto atualizadas com sucesso!');
    }

    public function toggleFavorite(Project $project)
    {
        $userId = $this->userId();

        $relation = $project->users()->where('users.user_id', $userId)->first();
        $this->authorize('view',$project);
/*         if (!$relation) {
            return response()->json(['error' => 'Not in project'], 403);
        } */

        $current = $relation->pivot->is_favorite;

        $project->users()->updateExistingPivot($userId, [
            'is_favorite' => !$current
        ]);

        return response()->json([
            'success' => true,
            'is_favorite' => !$current
        ]);
    }

    public function workload(Project $project){
            $this->ensureProjectMembership($project);
            $project->load('users', 'tasks');
    $workload = $project->users->map(function($user) use ($project) {
        $assignedTasks = $project->tasks->where('assignee_id', $user->user_id);
        $responsibleTasks = $project->tasks->where('task_responsible_id', $user->user_id);

        $totalTasks = $assignedTasks->count() + $responsibleTasks->count();
        //Isto é adição gira 
        $index = 0;
        foreach ($assignedTasks as $t) {
            $index += match($t->status) {
                'Untouched' => 1.5,
                'InProgress' => 1,
                'Done' => 0,
                default => 0,
            };
        }
        foreach ($responsibleTasks as $t) {
            $index += match($t->status) {
                'Untouched' => 0.5, //Ser responsavel é menos custoso do que ser quem as faz
                'InProgress' => 0.1,
                'Done' => 0,
                default => 0,
            };
        }

        return [
            'user' => $user->username,
            'assigned_count' => $assignedTasks->count(),
            'responsible_count' => $responsibleTasks->count(),
            'total_tasks' => $totalTasks,
            'workload_index' => $index,
        ];
    });

    return view('pages.project_analytics', [
        'project' => $project,
        'workload' => $workload,
    ]);
    }

    
}
