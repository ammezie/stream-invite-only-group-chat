<?php

namespace App\Http\Controllers;

use App\Invite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    public function create()
    {
        return view('invites.create');
    }

    public function store(Request $request)
    {
        $invite = Invite::firstWhere('email', $request->email);

        if (!is_null($invite)) {
            if ($invite->accepted) {
                return back()->with('status', 'User is already a member');
            } else {
                return back()->with('status', 'An invite has been sent to user already');
            }
        }

        $invite = Invite::create([
            'email' => $request->email,
            'token' => Str::random(40),
        ]);

        $mailBody = 'You\'ve been invited to join Chatroom: ' . url('invites', [$invite->token]);

        Mail::raw($mailBody, function ($message) use ($invite) {
            $message->to($invite->email);
            $message->from('chimezie@adonismastery.com');
            $message->subject('Join Chatroom');
        });

        return back()->with('status', 'Invite sent!');
    }

    public function show($token)
    {
        $invite = Invite::where('token', $token)->firstOrFail();

        if ($invite->accepted) {
            return redirect()->route('home');
        }

        return view('auth.register', compact('invite'));
    }
}
