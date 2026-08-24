<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;


class TaskController extends Controller
{
    // ============================================================
    // VIEW TASK LIST PAGE
    // ============================================================
    public function index(Request $request, Project $project)
    {
        $user = $this->user();
        $this->ensureProjectMembership($project);
        $this->authorize('view', $project);
        //Aqui o ensuremembership substitui a polic
        $base = Task::visibleTo($this->userId(), $project->project_id);

        $allowedSorts = ['name', 'status', 'priority', 'due_at', 'created_at', 'responsible', 'assignee'];
        $sortField = $request->input('sort_all', 'due_at');
        if (! in_array($sortField, $allowedSorts, true)) {
            $sortField = 'due_at';
        }
        $sortDirection = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // -------- ALL TASKS --------
        $allTasks = (clone $base)
            ->search($request->search_all)
            ->orderByField($sortField, $sortDirection)
            ->get()
            ->map(fn($t) => $t->toArrayForProject($user));

        $userRole = $project->roleOf($this->userId());
        $isCoordinator = $userRole === 'coordinator';

        // Coordinator does not have personal task lists
        $myTasks = $isCoordinator ? [] :
            (clone $base)
                ->where('assignee_id', $this->userId())
                ->search($request->search_mine)
                ->latest()
                ->get()
                ->map(fn($t) => $t->toArrayForProject($user));

        $responsibleTasks = $isCoordinator ? [] :
            (clone $base)
                ->where('task_responsible_id', $this->userId())
                ->latest()
                ->get()
                ->map(fn($t) => $t->toArrayForProject($user));

        $taskGroups = $project->taskGroups()
            ->with([
                'creator',
                'lists.creator',
                'lists.tasks' => fn($q) => $q->with(['responsible', 'assignee'])->orderBy('due_at')
            ])
            ->get()
            ->map(function ($group) use ($user) {
                $group->lists->transform(function ($list) use ($user) {
                    $list->tasks_for_display = $list->tasks->map(fn($task) => $task->toArrayForProject($user));
                    return $list;
                });
                return $group;
            });

        $taskListOptions = $project->taskLists()
            ->with('group')
            ->get()
            ->map(fn($list) => [
                'id'        => $list->task_list_id,
                'name'      => $list->name,
                'group'     => $list->group?->name,
            ]);

        $assignableTasks = $project->tasks()
            ->with('taskList')
            ->orderBy('name')
            ->get()
            ->map(fn($task) => [
                'id'    => $task->task_id,
                'name'  => $task->name,
                'list'  => $task->taskList?->name,
            ]);

        $viewData = [
            'user'              => $user,
            'project'           => $project,
            'allTasks'          => $allTasks,
            'myTasks'           => $myTasks,
            'responsibleTasks'  => $responsibleTasks,
            'summary'           => $project->computeSummary(),
            'userRole'          => $userRole,
            'isCoordinator'     => $isCoordinator,
            'taskSortField'     => $sortField,
            'taskSortDirection' => $sortDirection,
            'taskSortOptions'   => $allowedSorts,
            'searchAll'         => $request->search_all,
            'searchMine'        => $request->search_mine,
            'taskGroups'        => $taskGroups,
            'taskListOptions'   => $taskListOptions,
            'assignableTasks'   => $assignableTasks,
        ];

        if ($request->ajax() && $request->boolean('only_tasks')) {
            return response()->view('partials.tasks.tasks_list', $viewData);
        }

        return view('pages.tasks_list', $viewData);
    }

    // ============================================================
    // CREATE TASK
    // ============================================================
   public function store(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('create', [Task::class, $project]);
        $role = $project->roleOf($this->userId());


        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:256'],
            'priority'    => ['required', 'in:Urgent,High,Medium,Low'],
            'due_at'      => ['required', 'date'],

            'responsible' => $role === 'coordinator'
                                ? ['required', 'email']
                                : ['nullable'],

            'assignee'    => ['nullable', 'email'],
        ]);


        if ($role === 'coordinator') {
            $responsibleId = $project->emailToProjectUserId($data['responsible']);
            $assigneeId    = $project->emailToProjectUserId($data['assignee'] ?? null);

            if ($responsibleId === $this->userId()) {
                return back()->with('error',
                    'O coordenador não pode ser responsável da tarefa.'
                );
            }

            if ($assigneeId === $this->userId()) {
                return back()->with('error',
                    'O coordenador não pode ser atribuído à tarefa.'
                );
            }
            } 
            else {
            $responsibleId = $this->userId();
            $assigneeId = null;
        }

        // ------------------------------
        // CREATE TASK
        // ------------------------------
    $task = Task::create([
        ...$data,
        'status'              => 'Untouched',
        'effort'              => 'Medium',
        'nr_comment'          => 0,
        'created_at'          => now(),
        'project_id'          => $project->project_id,
        'task_responsible_id' => $responsibleId,
        'assignee_id'         => $assigneeId,
    ]);
    if ($assigneeId) {
        $task->notifyAssignment($assigneeId);
    }

        return redirect()
            ->route('tasks.index', $project)
            ->with('success', 'Tarefa criada com sucesso.');
    }

    // ============================================================
    // UPDATE TASK
    // ============================================================
    public function update(Request $request, Task $task)
    {
        $this->ensureProjectMembership($task->project);
        $this->authorize('update', $task);

        $previousStatus   = $task->status;
        $previousAssignee = $task->assignee_id;
        $role             = $task->roleOf($this->userId());
        $isResponsible    = $task->task_responsible_id === $this->userId();


        if ($request->has('status') && ! $request->has('name')) {

            if ($previousStatus !== 'Done' && $request->status === 'Done') {
                $task->notifyMarkAsDone();
            } else {
                $task->status = $request->status;
                $task->save();
            }

            return back();
        }


        $data = $request->validate([
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'status'      => ['required', 'in:Untouched,InProgress,Done'],
            'priority'    => ['required', 'in:Urgent,High,Medium,Low'],
            'due_at'      => ['required', 'date'],
            'responsible' => ['nullable', 'integer'],
            'assignee'    => ['nullable', 'integer'],
            'tags'=> 'nullable|string'
        ]);

        $project = $task->project;

        $markingAsDone = (
            $previousStatus !== 'Done'
            && ($data['status'] ?? null) === 'Done'
        );

        if ($role === 'coordinator') {

            if (
                array_key_exists('responsible', $data) &&
                ! $project->users
                    ->where('pivot.user_role', '!=', 'coordinator')
                    ->contains('user_id', $data['responsible'])
            ) {
                return back()->withErrors([
                    'responsible' => 'O responsável não pode ser coordenador.'
                ]);
            }

            if (
                array_key_exists('assignee', $data) &&
                $data['assignee'] &&
                ! $project->users
                    ->where('pivot.user_role', '!=', 'coordinator')
                    ->contains('user_id', $data['assignee'])
            ) {
                return back()->withErrors([
                    'assignee' => 'O assignee não pode ser coordenador.'
                ]);
            }

            if (array_key_exists('responsible', $data)) {
                $task->task_responsible_id = $data['responsible'];
            }

            if (array_key_exists('assignee', $data)) {
                $task->assignee_id = $data['assignee'] ?: null;
            }
        } elseif ($isResponsible) {

            if (
                array_key_exists('assignee', $data) &&
                $data['assignee'] &&
                ! $project->users
                    ->where('pivot.user_role', '!=', 'coordinator')
                    ->contains('user_id', $data['assignee'])
            ) {
                return back()->withErrors([
                    'assignee' => 'O assignee não pode ser coordenador.'
                ]);
            }

            if (array_key_exists('assignee', $data)) {
                $task->assignee_id = $data['assignee'] ?: null;
            }

            // Responsáveis não podem alterar o responsável
            unset($data['responsible']);
        }

       // ---------------- TAGS ----------------
        if (array_key_exists('tags', $data)) {
            $tagIds = collect(explode(',', $data['tags']))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->toArray();

            $task->tags()->sync($tagIds);
        } else {
            $task->tags()->detach();
        }

      
        if ($markingAsDone) {
            unset($data['status']);
        }

        $task->fill($data);
        $task->save();

       
        if ($task->assignee_id && $task->assignee_id !== $previousAssignee) {
            $task->notifyAssignment($task->assignee_id);
        }

        if ($markingAsDone) {
            $task->notifyMarkAsDone();
        }

        return back()->with('success', 'Tarefa atualizada.');
    }


    // ============================================================
    // DELETE TASK
    // ============================================================
    public function destroy(Task $task)
    {
        $this->ensureProjectMembership($task->project);

  /*       if (!$task->canBeDeletedBy($this->user())) {
            abort(403, 'Acess denied. No permission');
        } */
       $this->authorize('delete',$task);

        $project = $task->project;
        $task->delete();

        return redirect()
            ->route('tasks.index', $project)
            ->with('success', 'Tarefa removida.');
    }


    // ============================================================
    // SHOW TASK PAGE
    // ============================================================
    public function show(Task $task)
    {
        $this->ensureProjectMembership($task->project);
        $this->authorize('view', $task);

        $user = $this->user();

        $task->load([
            'responsible',
            'assignee',
            'predecessorLinks.predecessor',
            'successorLinks.successor',
            'taskComments.author',
            'project.tasks',
            'project.users',
        ]);

        $role = $task->roleOf($user->user_id);

        $initialComments = $task->taskComments->map(function ($comment) use ($user, $role) {
            return [
                'id'         => $comment->comment_id,
                'message'    => $comment->message_,
                'author'     => $comment->author?->username ?? 'Utilizador removido',
                'created_at' => optional($comment->created_at)->format('d/m/Y'),
                'can_delete' => $comment->user_id === $user->user_id || $role === 'coordinator',
            ];
        })->values()->all();

        $initialDependencies = [
            'predecessors' => $task->predecessorLinks->map(fn($link) => [
                'id'      => $link->task_dependency_id,
                'task_id' => $link->predecessor?->task_id,
                'name'    => $link->predecessor?->name,
                'status'  => $link->predecessor?->status,
            ])->values()->all(),
            'successors' => $task->successorLinks->map(fn($link) => [
                'id'      => $link->task_dependency_id,
                'task_id' => $link->successor?->task_id,
                'name'    => $link->successor?->name,
                'status'  => $link->successor?->status,
            ])->values()->all(),
        ];

        $availableTasks = $task->project->tasks
            ->where('task_id', '!=', $task->task_id)
            ->sortBy('name')
            ->values();

        return view('pages.individual_task', [
            'user'                   => $user,
            'task'                   => $task->toArrayForProject($user),
            'taskModel'              => $task,
            'project'                => $task->project,
            'members'                => $task->project->users,
            'summary'                => $task->project->computeSummary(),
            'initialComments'        => $initialComments,
            'initialDependencies'    => $initialDependencies,
            'availableTasks'         => $availableTasks,
            'canManageDependencies'  => $role === 'coordinator' || $task->task_responsible_id === $user->user_id,
        ]);
    }
}
