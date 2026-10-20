<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    /** Messages are stored for staff to read in the admin panel (no mail server is assumed). */
    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:100',
            'message' => 'required|string|min:10|max:3000',
        ]);

        ContactMessage::create($data + ['user_id' => auth()->id()]);

        return redirect()->route('contact')
            ->with('success', 'Thank you. Your message has been received and the pharmacy team will reply to your email.');
    }
}
