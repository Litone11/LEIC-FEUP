
<?php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use App\Events\NotificationEvent;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::call(function () {
    $tasks = Task::where('status', '!=', 'Done')  
            ->where('due_at', '>=', now())
            ->where('due_at', '<=', now()->addDay())
            ->get();
    foreach ($tasks as $task) {
        $recipients = [];
        // Assignee
        if ($task->assignee_id) {
            $recipients[] = $task->assignee_id;
        }
        // Coordenador do projeto
        $coordinatorId = $task->project?->users()
            ->wherePivot('user_role', 'coordinator')
            ->pluck('users.user_id')
            ->first();
        if ($coordinatorId && !in_array($coordinatorId, $recipients)) {
            $recipients[] = $coordinatorId;
        }
        // Criar notificações
        foreach ($recipients as $userId) {
            $notification = Notification::create([
                'receiver_id' => $userId,
                'title' => 'Tarefa próxima do prazo',
                'message_' => "A tarefa '{$task->name}' está próxima do prazo e ainda não foi concluída.",
            ]);
            if ($notification) {
                event(new NotificationEvent($notification));
                Log::info("Due task notification sent for task: {$task->name} to user_id: $userId");
            }
        }
    }
})->daily(); //trocar para everyMinute()