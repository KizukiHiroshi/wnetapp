<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class MailController extends Controller
{
    public function send(Request $request) {
        $content = $request->input('content'); 
        $user = Auth::user();
	
	Mail::to($user->email)->send(new SendMail($content));
	
	
	// メール送信後の処理
	
    }
}