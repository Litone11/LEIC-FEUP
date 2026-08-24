<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\TaskList;
use Illuminate\Http\Request;

class TaskListController extends Controller
{
    public function storeGroup(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission', $project);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:256'],
            'label'       => ['nullable', 'string', 'max:80'],
        ]);

        TaskGroup::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'label'       => $data['label'] ?? null,
            'project_id'  => $project->project_id,
            'created_by'  => $this->userId(),
        ]);

        return back()->with('success', 'Grupo de listas criado com sucesso.');
    }

    public function storeList(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission', $project);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:256'],
            'group_id'    => ['required', 'integer'],
        ]);

        $group = TaskGroup::where('project_id', $project->project_id)
            ->where('task_group_id', $data['group_id'])
            ->firstOrFail();

        TaskList::create([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'task_group_id' => $group->task_group_id,
            'created_by'    => $this->userId(),
        ]);

        return back()->with('success', 'Lista criada com sucesso.');
    }

    public function assign(Request $request, Project $project, TaskList $taskList)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission', $project);

        abort_unless($taskList->group?->project_id === $project->project_id, 404);

        $data = $request->validate([
            'task_id' => ['required', 'integer'],
        ]);

        $task = $project->tasks()
            ->where('task_id', $data['task_id'])
            ->firstOrFail();

        $task->update(['task_list_id' => $taskList->task_list_id]);

        return back()->with('success', 'Tarefa atribuída à lista.');
    }

    public function assignFromCard(Request $request, Project $project, Task $task)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('coordinatorPermission', $project);
        abort_unless($task->project_id === $project->project_id, 404);

        $data = $request->validate([
            'task_list_id' => ['nullable', 'integer'],
        ]);

        $listId = $data['task_list_id'];
        if ($listId) {
            $list = TaskList::where('task_list_id', $listId)
                ->whereHas('group', fn($q) => $q->where('project_id', $project->project_id))
                ->firstOrFail();
            $task->update(['task_list_id' => $list->task_list_id]);
        } else {
            $task->update(['task_list_id' => null]);
        }

        return back()->with('success', 'Lista da tarefa atualizada.');
    }

    public function destroyGroup(Project $project, TaskGroup $taskGroup)
    {
        $this->ensureProjectMembership($project);
        abort_unless($taskGroup->project_id === $project->project_id, 404);
        $this->authorize('delete', $taskGroup);

        $taskGroup->load('lists.tasks');

        foreach ($taskGroup->lists as $list) {
            $list->tasks()->update(['task_list_id' => null]);
            $list->delete();
        }

        $taskGroup->delete();

        return back()->with('success', 'Grupo removido com sucesso.');
    }

    public function destroyList(Project $project, TaskList $taskList)
    {
        $this->ensureProjectMembership($project);
        $taskList->load('group');
        abort_unless($taskList->group?->project_id === $project->project_id, 404);
        $this->authorize('delete', $taskList);

        $taskList->tasks()->update(['task_list_id' => null]);
        $taskList->delete();

        return back()->with('success', 'Lista removida com sucesso.');
    }
}
