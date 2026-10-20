<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) return redirect('/');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'city'     => 'nullable|string|max:100',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique'       => 'This email is already registered. Please login.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min'       => 'Password must be at least 8 characters.',
        ]);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'city'              => $request->city,
            'password'          => Hash::make($request->password),
            'role'              => 'customer',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->route('account.index')
            ->with('success', 'Welcome to City Drug, ' . $user->name . '! Your account has been created.');
    }
}
