<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class MailController extends Controller
{
    // ★★　何でワカラン…時間がかかり過ぎなので一旦飛ばす
    public function send(Request $request){
    	// $data = [];

    	// Mail::send('mail', $data, function($message){
    	//     $message->to('altoids@wisecorp.net', 'Test')
        //         ->from('wnet@wisecorp.net','Reffect')
        //         ->subject('This is a test mail');
        // });

        $to = [
            [
                'name' => 'altoids', 
                'email' => 'altoids@wisecorp.net', 
            ]
        ];
        // $from = 'wnet@wisecorp.net';
        // $subject = 'mailsubject2';
        // $body = 'mailbody2';
        // $files =[];
        Mail::to($to)->send(new SendMail());
    }
}
