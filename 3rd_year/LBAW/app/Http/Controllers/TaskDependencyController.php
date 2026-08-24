<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\Request;

class TaskDependencyController extends Controller
{
    public function index(Task $task)
    {
        $this->ensureProjectMembership($task->project);
        $this->authorize('view', $task);

        return response()->json([
            'predecessors' => $task->predecessorLinks
                ->map(fn($link) => $this->formatLink($link->task_dependency_id, $link->predecessor))
                ->filter()
                ->values(),
            'successors' => $task->successorLinks
                ->map(fn($link) => $this->formatLink($link->task_dependency_id, $link->successor))
                ->filter()
                ->values(),
        ]);
    }

    public function store(Request $request, Task $task)
    {
        $this->ensureProjectMembership($task->project);
        $this->authorize('manageDependencies', $task);

        $data = $request->validate([
            'type'    => ['required', 'in:predecessor,successor'],
            'task_id' => ['required', 'integer'],
        ]);

        $candidate = $task->project->tasks()
            ->where('task_id', $data['task_id'])
            ->firstOrFail();

        if ($candidate->task_id === $task->task_id) {
            return $this->dependencyError($request, 'Não é possível criar uma dependência da tarefa para si própria.');
        }

        [$predecessorId, $successorId] = $data['type'] === 'predecessor'
            ? [$candidate->task_id, $task->task_id]
            : [$task->task_id, $candidate->task_id];

        if ($this->wouldCreateCycle($predecessorId, $successorId)) {
            return $this->dependencyError($request, 'Esta ligação criaria um ciclo entre tarefas.');
        }

        $exists = TaskDependency::where('predecessor_task_id', $predecessorId)
            ->where('successor_task_id', $successorId)
            ->exists();

        if ($exists) {
            return $this->dependencyError($request, 'A dependência já existe.', 409);
        }

        $dependency = TaskDependency::create([
            'predecessor_task_id' => $predecessorId,
            'successor_task_id'   => $successorId,
            'created_at'          => now(),
        ]);

        $dependency->load('predecessor', 'successor');

        $linkedTask = $data['type'] === 'predecessor'
            ? $dependency->predecessor
            : $dependency->successor;

        $payload = [
            'dependency' => $this->formatLink($dependency->task_dependency_id, $linkedTask),
            'type'       => $data['type'],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, 201);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Dependência adicionada.');
    }

    public function destroy(Request $request, Task $task, TaskDependency $dependency)
    {
        $this->ensureProjectMembership($task->project);

        if (
            $dependency->predecessor_task_id !== $task->task_id &&
            $dependency->successor_task_id !== $task->task_id
        ) {
            abort(404);
        }

        $this->authorize('manageDependencies', $task);

        $dependency->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Dependência removida.');
    }

    protected function formatLink(int $id, ?Task $linkedTask): ?array
    {
        if (!$linkedTask) {
            return null;
        }

        return [
            'id'      => $id,
            'task_id' => $linkedTask->task_id,
            'name'    => $linkedTask->name,
            'status'  => $linkedTask->status,
        ];
    }

    protected function wouldCreateCycle(int $predecessorId, int $successorId): bool
    {
        if ($predecessorId === $successorId) {
            return true;
        }

        $visited = [];
        $stack = [$successorId];

        while ($stack) {
            $current = array_pop($stack);

            if ($current === $predecessorId) {
                return true;
            }

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            $children = TaskDependency::where('predecessor_task_id', $current)
                ->pluck('successor_task_id')
                ->all();

            foreach ($children as $child) {
                if (!isset($visited[$child])) {
                    $stack[] = $child;
                }
            }
        }

        return false;
    }

    protected function dependencyError(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->withErrors(['dependencies' => $message]);
    }
}
