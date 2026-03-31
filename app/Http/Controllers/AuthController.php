<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('login.login');
    }

    public function authenticate(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('home')
                ->with('success', 'Tizimga muvaffaqiyatli kirdingiz.')
                ->with('toast_type', 'success');
        }

        return back()
            ->withErrors([
                'email' => "Email yoki parol noto'g'ri.",
            ])
            ->onlyInput('email');
    }

    public function register()
    {
        return view('login.regiter');
    }

    public function registerStore(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Ro‘yxatdan o‘tish muvaffaqiyatli yakunlandi.')
            ->with('toast_type', 'success');
    }

    public function regiter_store(RegisterRequest $request)
    {
        return $this->registerStore($request);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('error', 'Siz tizimdan chiqdingiz.')
            ->with('toast_type', 'error');
    }
}
