<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SuspendProject;
use Illuminate\Http\Request;

class AdminProjectController extends Controller
{
    // List all projects for the admin panel
    public function index()
    {
        $projects = Project::withTaskStats()
            ->withMemberCount()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->project_id,
                'name'         => $p->name,
                'description'  => $p->description,
                'members'      => $p->teamCount(),
                'coordinator'=> $p->getCoordinator()->username ??"null",
                'created_at'   => $p->created_at?->format('d M Y'),
                'status'       => $p->status_label,
                'progress'     => $p->progress,
                'is_archived'  => (bool) $p->is_archived,
                'is_suspended' => $p->isSuspended(),
                'suspension_reason' => $p->suspensionReason(),
            ]);

        return view('pages.admin_projects_list', [
            'user'     => auth()->user(),
            'projects' => $projects,
        ]);
    }

    // Detailed project view
    public function show(Project $project)
    {
        $admin = $this->user();
        abort_unless($admin->isAdmin(), 403);

        $project->load([
            'users' => fn($query) => $query->select('users.user_id', 'users.username', 'users.email'),
            'tasks' => fn($query) => $query
                ->with([
                    'responsible:user_id,username',
                    'assignee:user_id,username',
                ])
                ->orderByDesc('created_at')
                ->limit(8),
            'latestSuspension',
        ]);

        $members = $project->users->map(function ($member) {
            return [
                'id'       => $member->user_id,
                'username' => $member->username,
                'email'    => $member->email,
                'role'     => $member->pivot?->user_role ?? 'member',
            ];
        })->sortBy(fn($member) => $member['role'] === 'coordinator' ? 0 : 1)->values();

        $recentTasks = $project->tasks->map(fn($task) => [
            'id'          => $task->task_id,
            'name'        => $task->name,
            'status'      => $task->status,
            'priority'    => $task->priority,
            'responsible' => $task->responsible?->username,
            'assignee'    => $task->assignee?->username,
            'created_at'  => $task->created_at?->format('M d, Y'),
            'due_at'      => $task->due_at?->format('M d, Y'),
        ]);

        $summary = $project->computeSummary();

        return view('pages.admin_project_details', [
            'user'        => $admin,
            'project'     => $project,
            'summary'     => $summary,
            'members'     => $members,
            'recentTasks' => $recentTasks,
        ]);
    }

    public function suspend(Request $request, Project $project)
    {
        $admin = $this->user();
        abort_unless($admin->isAdmin(), 403);

        if ($project->isSuspended()) {
            $message = 'Projeto já se encontra suspenso.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:250'],
        ]);

        SuspendProject::create([
            'reason'         => $validated['reason'],
            'suspended_at'   => now(),
            'unsuspended_at' => now(),
            'admin_id'       => $admin->user_id,
            'project_id'     => $project->project_id,
        ]);

        $message = 'Projeto suspenso com sucesso.';
        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    public function unsuspend(Request $request, Project $project)
    {
        $admin = $this->user();
        abort_unless($admin->isAdmin(), 403);

        $suspension = $project->latestSuspension()->first();

        if (! $suspension) {
            $message = 'Projeto não está suspenso.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $suspension->delete();

        $message = 'Projeto reativado com sucesso.';
        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    public function destroy(Request $request, Project $project)
    {
        $admin = $this->user();
        abort_unless($admin->isAdmin(), 403);

        $project->getConnection()->statement('CALL proc_tran08(?)', [$project->project_id]);

        $message = 'Projeto eliminado com sucesso.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('admin.dashboard', ['tab' => 'projects'])
            ->with('success', $message);
    }
}
