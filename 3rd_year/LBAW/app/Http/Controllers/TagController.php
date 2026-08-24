<?php
namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Task;

use App\Models\Project;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        // adicionar una failsafe melhor
        if (!$projectId) {
            return response()->json(['error' => 'Sem project Id'], 400);
        }

        $project = Project::findOrFail($projectId);
        $this->authorize('view', $project);

        $tags = Tag::where('project_id', $projectId)->get();
        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'project_id' => 'required|exists:project,project_id',
        ]);
        $project = Project::findOrFail($data['project_id']);
        $this->authorize('create', [Tag::class, $project]);

        $tag = Tag::create($data);
        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete',$tag);
        $tag->tasks()->detach(); 
        $tag->delete();
        return response()->json(['success' => true]);
    }
    public function taskTags(Request $request)
{
    $taskId = $request->query('task_id');
    if (!$taskId) return response()->json(['error' => 'Task ID required'], 400);

    $task = Task::findOrFail($taskId);
    $this->authorize('view', $task);
    return response()->json($task->tags);
}
public function attachTag(Request $request)
{
    $data = $request->validate([
        'task_id' => 'required|exists:task,task_id',
        'name'    => 'required|string|max:50'
    ]);

    $task = Task::findOrFail($data['task_id']);
    $this->authorize('update', $task);

    // Desta froma o cordenator nao tem de saber as coisas de cor e pode sempre remover no projeto
    $tag = Tag::firstOrCreate([
        'name'       => $data['name'],
        'project_id' => $task->project_id,
    ]);

    $task->tags()->syncWithoutDetaching($tag->tag_id);

    return response()->json($tag);
}
public function detachTag(Request $request, $tagId)
{
    $taskId = $request->query('task_id');
    if (!$taskId) return response()->json(['error' => 'Task ID required'], 400);

    $task = Task::findOrFail($taskId);
    $this->authorize('update', $task);
    $task->tags()->detach($tagId);

    return response()->json(['success' => true]);
}


}
