<?php

namespace App\Http\Controllers;

use App\Models\ForumReply;
use App\Models\ForumLike;
use App\Models\ForumTopic;
use App\Models\Project;
use App\Models\Notification;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index(Project $project)
    {
        $this->ensureProjectMembership($project);

        $topics = ForumTopic::with(['author', 'task'])
            ->withCount(['replies', 'likes'])
            ->where('project_id', $project->project_id)
            ->orderByDesc('created_at')
            ->orderByDesc('forum_topic_id')
            ->get();

        return view('pages.project_forum', [
            'user'    => $this->user(),
            'project' => $project,
            'summary' => $project->computeSummary(),
            'topics'  => $topics,
            'tasks'   => $project->tasks()->orderBy('name')->get(['task_id', 'name']),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body'  => ['required', 'string', 'max:2000'],
            'task_id' => ['nullable', 'integer'],
        ]);

        $taskId = $data['task_id'] ?? null;
        if ($taskId && ! $project->tasks()->where('task_id', $taskId)->exists()) {
            return back()->withErrors(['task_id' => 'A tarefa selecionada não pertence a este projeto.'])->withInput();
        }

        $topic = ForumTopic::create([
            'title'      => $data['title'],
            'body'       => $data['body'],
            'created_at' => now()->toDateString(),
            'project_id' => $project->project_id,
            'user_id'    => $this->userId(),
            'task_id'    => $taskId,
        ]);

        return redirect()
            ->route('projects.forum.topic', [$project, $topic])
            ->with('success', 'Tópico criado com sucesso.');
    }

    public function show(Project $project, ForumTopic $topic)
    {
        $this->ensureProjectMembership($project);

        abort_unless($topic->project_id === $project->project_id, 404);

        $topic->load(['author', 'replies.author', 'task', 'likes']);

        return view('pages.project_forum_topic', [
            'user'    => $this->user(),
            'project' => $project,
            'summary' => $project->computeSummary(),
            'topic'   => $topic,
            'liked'   => $topic->likedByUser($this->userId()),
        ]);
    }

    public function reply(Request $request, Project $project, ForumTopic $topic)
    {
        $this->ensureProjectMembership($project);
        abort_unless($topic->project_id === $project->project_id, 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1500'],
        ]);

        ForumReply::create([
            'body'       => $data['body'],
            'created_at' => now()->toDateString(),
            'topic_id'   => $topic->forum_topic_id,
            'user_id'    => $this->userId(),
        ]);

        // Notify topic owner if someone else replied
        if ($topic->user_id !== $this->userId()) {
            Notification::create([
                'title'       => 'Nova resposta no fórum',
                'message_'    => 'Alguém respondeu à tua thread no projeto "' . $project->name . '".',
                'created_at'  => now(),
                'receiver_id' => $topic->user_id,
                'is_read'     => false,
                'link'        => route('projects.forum.topic', [$project, $topic]),
            ]);
        }

        return redirect()
            ->route('projects.forum.topic', [$project, $topic])
            ->with('success', 'Resposta adicionada.');
    }

    public function like(Request $request, Project $project, ForumTopic $topic)
    {
        $this->ensureProjectMembership($project);
        abort_unless($topic->project_id === $project->project_id, 404);

        $userId = $this->userId();
        $likeQuery = ForumLike::where('topic_id', $topic->forum_topic_id)
            ->where('user_id', $userId);
        $existing = $likeQuery->exists();

        $liked = false;
        if ($existing) {
            $likeQuery->delete();
        } else {
            ForumLike::create([
                'user_id'  => $userId,
                'topic_id' => $topic->forum_topic_id,
                'liked_at' => now()->toDateString(),
            ]);
            $liked = true;

            if ($topic->user_id !== $userId) {
                Notification::create([
                    'title'       => 'Novo like no fórum',
                    'message_'    => 'Alguém gostou da tua thread no projeto "' . $project->name . '".',
                    'created_at'  => now(),
                    'receiver_id' => $topic->user_id,
                    'is_read'     => false,
                    'link'        => route('projects.forum.topic', [$project, $topic]),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['liked' => $liked]);
        }

        return back()->with('success', $liked ? 'Gostaste do tópico.' : 'Removeste o gosto.');
    }
}
