<?php

namespace App\Http\Controllers;

use App\Mail\ContactUs;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    public function store(Request $request){
        $rules = [
            'email' => 'required|email',
            'subject' => 'required|string|max:191',
            'message' => 'required|string|max:191'
        ];
        $validator = validator($request->all(), $rules);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()]);
        }
        $validated = $validator->validated();
        if(auth()->user()->shop_contacts()->create($validated)):
            Mail::send('emails.contactUs', ['msg' => $validated['message']], function ($message) use ($validated) {
                $message->from($validated['email'] ? $validated['email'] : auth()->user()->email );
                $message->subject($validated['subject']);
                $message->to(config('shopifyApi.strings.userEmailAddress'));
            });
            // Mail::to('alpha_currency@alpha-currency-converter-app.com')->from($validated['email'] ? $validated['email'] : auth()->user()->name )->send(new ContactUs($validated));
            return response()->json(['success' => "Contact Successfull."]);
        endif;
        return response()->json(['error' => "Something Went Wrong."]);
    }
}
