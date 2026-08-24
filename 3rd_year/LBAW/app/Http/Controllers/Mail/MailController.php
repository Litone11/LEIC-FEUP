<?php

namespace App\Http\Controllers\Mail;

use Illuminate\Http\Request;
class MailController extends Controller
{
    function send(Request $request) {

        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        Mail::to($request->email)->send(new MailModel($mailData));
        return redirect()->route('home');
    }
}

