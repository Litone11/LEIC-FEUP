<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\SearchService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

   public function search(Request $request)
    {
        $search = trim($request->query('search', ''));
        $userId = $this->userId();

        if ($search === '') {
            return view('partials.dashboard.search_results', [
                'projects' => [],
                'tasks' => []
            ]);
        }

        $results = $this->searchService->searchAll($userId, $search);

        return view('partials.dashboard.search_results', [
            'projects' => $results['projects'],
            'tasks'    => $results['tasks'],
        ]);
    }

   public function __invoke()
    {
        $user = $this->user();
        $userProjectIds = $user->projectIds();

        // If user belongs to no projects
        if ($userProjectIds->isEmpty()) {
            return view('pages.dashboard', [
                'user'        => $user,
                'projects'    => collect(),
                'stats'       => [
                    'total_projects'  => 0,
                    'completed_tasks' => 0,
                    'active_tasks'    => 0,
                ],
                'recentTasks' => collect(),
            ]);
        }

        // PROJECT STATS
        $projectStats = Project::query()
            ->forUser($user->user_id)
            ->withTaskStats()
            ->orderByDesc('created_at')
            ->limit(4)
            ->get()
            ->map(fn($p) => [
                'id'             => $p->project_id,
                'name'           => $p->name,
                'description'    => $p->description,
                'tasks_total'    => $p->total_tasks,
                'tasks_done'     => $p->completed_tasks,
                'progress'       => $p->progress,
                'is_coordinator' => $p->isCoordinatorFor($user->user_id),
            ]);

        // TOTAL STATS
        $stats = [
            'total_projects'  => $userProjectIds->count(),
            'completed_tasks' => $user->completedTaskCount(),
            'active_tasks'    => $user->activeTaskCount(),
        ];

        // RECENT TASKS
        $recentTasks = Task::with('project')
            ->whereIn('project_id', $userProjectIds)
            ->orderByDesc('created_at')
            ->limit(4)
            ->get()
            ->map(fn($task) => [
                'title'       => $task->name,
                'description' => $task->description,
                'project'     => $task->project?->name,
                'done'        => $task->status === 'Done',
            ]);

    
        return view('pages.dashboard', [
            'user'        => $user,
            'projects'    => $projectStats,
            'stats'       => $stats,
            'recentTasks' => $recentTasks,
        ]);
    }

}
