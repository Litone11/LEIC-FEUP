<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\MailModel;
use Illuminate\Support\Facades\Mail;
use App\Models\Invitation;

use App\Models\Project;
use App\Events\NotificationEvent;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\DB;




class InvitationController extends Controller
{

    public function index(){
            $user=$this->user(); //maybe auth()user
           return view('pages.invitations', [
            'user' => $user,
            'invitations' => $user->invitations(),
        ]);

    }


    public function notifyInvitationAccepted(Invitation $invitation): void {
            DB::transaction(function () use ($invitation) {
                DB::statement('CALL proc_tran03(?)', [  $invitation->invitation_id]);
            });

            if ($notification = Notification::latest('notification_id')->first()) {
                event(new NotificationEvent($notification));
            }
        }
    public function send(Request $request, Project $project)
    {
        $this->ensureProjectMembership($project);
        $this->authorize('update', $project);
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $receiver = User::where('email', $request->email)->first();

        if (!$receiver) {
            return back()->with('error', 'Utilizador não encontrado.');
        }

        if ($project->users()->where('users.user_id', $receiver->user_id)->exists()) {
            return back()->with('error', 'Este utilizador já pertence ao projeto.');
        }

        $invitation = Invitation::create([
            'sender_id'   => $this->userId(),
            'receiver_id' => $receiver->user_id,
            'project_id'  => $project->project_id,
        ]);
        //Desta forma cria-se notifação e envia-se email
        $this->notifyInvitationReceived($project->project_id,$receiver->user_id);
        $mailData = [
            'subject' => "Convite para o projeto {$project->name}",
            'view'    => 'emails.project_invitation',
            'data'    => ['invitation' => $invitation],
        ];

        Mail::to($receiver->email)->send(new MailModel($mailData));

        return back()->with('success', 'Convite enviado com sucesso.');
    }

    public function accept(Invitation $invitation)
{
    if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Precisas de estar logado para aceitar o convite.');
    }
    $this->authorize('accept', $invitation);
    $userId = auth()->id();
    $project = $invitation->project;

    // Attach the user to the project
    $project->users()->syncWithoutDetaching([
        $userId => [
            'user_role'   => 'normal',
            'is_favorite' => false,
        ],
    ]);
    $invitation->is_accepted = true;
    $invitation->save();
    $this->notifyInvitationAccepted($invitation);
        $invitation->project->users()->syncWithoutDetaching([
        $userId => [
            'user_role' => 'normal',
            'is_favorite' => false,
        ],
    ]);
    return redirect()->route('projects.show', $invitation->project)
                     ->with('success', 'Entraste no projeto!');
}
public function decline(Invitation $invitation)
{
    $this->authorize('decline', $invitation);

    // Delete the invitation,
    // //ADD the policy

    $invitation->delete();

    return redirect()->route('invitations')
                     ->with('success', 'Convite recusado com sucesso.');
}



}
