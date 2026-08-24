<?php
namespace App\Services;

use App\Models\Project;
use App\Models\Notification;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;


class NotificationService
{
    public function notifyCoordinatorChange(Project $project): void
    {
        foreach ($project->users as $member) {
            DB::statement('CALL proc_tran01(?, ?)', [
                $project->project_id,
                $member->user_id
            ]);

            $notification = Notification::where('receiver_id', $member->user_id)
                ->latest('notification_id')
                ->first();

            if ($notification) {
                event(new NotificationEvent($notification));
            }
        }
    }

    public function notifyInvitationReceived( $project_id,$userId): void {

        DB::statement('CALL proc_tran02(?, ?)', [$project_id, $userId]);
        $notification = Notification::where('receiver_id', $userId)
                        ->latest('notification_id')
                        ->first();
        if($notification)
       { event(new NotificationEvent($notification));}
 
    }


}
