<?php

namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\Task;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function calendar()
    {
        $user = auth()->user();

        $projects = Project::forUser($user->user_id)
            ->select('project_id', 'name')
            ->orderBy('name')
            ->get();

        return view('pages.calendar', [
            'user'     => $user,
            'projects' => $projects,
        ]);
    }

   public function events(Request $request)
{
    $user = auth()->user();

    $projectIds = $request->input('projects', []);

    if (empty($projectIds)) {
        return response()->json([]);
    }

    $tasks = Task::with('project:project_id,color')
        ->whereHas('project.users', fn ($q) =>
            $q->where('users.user_id', $user->user_id)
        )
        ->whereNotNull('due_at')
        ->whereIn('project_id', $projectIds)
        ->get();

    return response()->json(
        $tasks->map(fn ($task) => [
            'id'    => $task->task_id,
            'title' => $task->name,
            'start' => $task->due_at->toDateString(),
            'url'   => route('tasks.show', $task),

            'backgroundColor' => $task->project->color,
            'borderColor'     => $task->project->color,
        ])
    );
}

}

