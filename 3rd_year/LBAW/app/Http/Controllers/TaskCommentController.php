<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function index(Task $task)
    {
        $this->ensureProjectMembership($task->project);
        $this->authorize('view', $task);

        $role = $task->roleOf($this->userId());

        $comments = $task->taskComments()
            ->with('author')
            ->orderBy('commentary.created_at')
            ->get()
            ->map(fn($comment) => $this->formatComment($comment, $role));

        return response()->json(['comments' => $comments]);
    }

    public function store(Request $request, Task $task)
    {
        $this->ensureProjectMembership($task->project);
        $this->authorize('view', $task);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:512'],
        ]);

        $thread = $task->ensureDiscussionThread($this->userId());

        $comment = TaskComment::create([
            'message_'  => $data['message'],
            'thread_id' => $thread->thread_id,
            'user_id'   => $this->userId(),
            'created_at'=> now()->toDateString(),
        ]);

        $task->increment('nr_comment');
        $comment->refresh()->load('author');

        $payload = [
            'comment' => $this->formatComment($comment, $task->roleOf($this->userId())),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, 201);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Comentário registado com sucesso.');
    }

    public function destroy(Request $request, Task $task, TaskComment $comment)
    {
        $this->ensureProjectMembership($task->project);

        if ($comment->thread?->task_id !== $task->task_id) {
            abort(404);
        }

        $this->authorize('delete', $comment);

        $comment->delete();
        $task->decrement('nr_comment');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Comentário removido.');
    }

    protected function formatComment(TaskComment $comment, ?string $role): array
    {
        return [
            'id'         => $comment->comment_id,
            'message'    => $comment->message_,
            'author'     => $comment->author?->username ?? 'Utilizador removido',
            'created_at' => optional($comment->created_at)->format('d/m/Y'),
            'can_delete' => $comment->user_id === $this->userId() || $role === 'coordinator',
        ];
    }
}
