<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-task-comments-wrapper>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-slate-900">Comentários</h3>
    </div>

    <div
        data-task-comments
        data-store-url="{{ route('tasks.comments.store', $taskModel) }}"
        data-delete-url-template="{{ route('tasks.comments.destroy', [$taskModel, '__ID__']) }}"
        data-initial-comments='@json($initialComments)'
    >
        <div class="space-y-3 mb-4" data-comments-list>
            @forelse ($initialComments as $comment)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3" data-comment-id="{{ $comment['id'] }}">
                    <div class="flex items-center justify-between text-sm">
                        <p class="font-medium text-slate-800">{{ $comment['author'] }}</p>
                        <span class="text-xs text-slate-500">{{ $comment['created_at'] }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">
                        {{ $comment['message'] }}
                    </p>
                    @if ($comment['can_delete'])
                        <form method="POST"
                              action="{{ route('tasks.comments.destroy', [$taskModel, $comment['id']]) }}"
                              class="mt-3 inline-flex"
                              data-comment-delete-form
                              data-comment-id="{{ $comment['id'] }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs text-rose-500 hover:text-rose-600"
                                    data-delete-comment="{{ $comment['id'] }}">
                                Remover
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500" data-comments-empty>Sem comentários nesta tarefa.</p>
            @endforelse
        </div>

        <form class="space-y-3"
              data-comment-form
              method="POST"
              action="{{ route('tasks.comments.store', $taskModel) }}">
            @csrf
            <textarea
                name="message"
                rows="3"
                class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-atlas-400 focus:ring-0"
                placeholder="Partilha atualizações ou feedback com a tua equipa."
                required
                maxlength="512"
            ></textarea>
            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-600">
                    <i class="bi bi-send"></i> Enviar comentário
                </button>
            </div>
        </form>
    </div>
</div>
