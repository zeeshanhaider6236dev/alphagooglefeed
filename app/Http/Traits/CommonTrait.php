<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Mail;

trait CommonTrait {

    public function welcomeEmail($data){
        Mail::send('emails.welcome', ['data' => $data], function ($mail ) use ($data){
            $mail->from('support@alpha-google-shopping-feed.com', 'ALPHA Google Shopping Feed App');
            $mail->to($data['email'])
            ->subject('Welcome to ALPHA Google Shopping Feed');
        });
    }

    public function UninstallEmail($data){
        Mail::send('emails.uninstall', ['data' => $data], function ($mail ) use ($data){
            $mail->from('support@alpha-google-shopping-feed.com', 'ALPHA Google Shopping Feed');
            $mail->to($data['email'])
            ->subject('[PENDING] Uninstall is not complete ⚠️ I can help 👋🏻');
        });
    }
}