<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }
        return back()->withErrors([
            'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showResetPasswordForm()
    {
        return view('auth.password-reset');
    }
    public function resetPassword(PasswordResetRequest $request)
    {
        if ($request->secretKey == 'شركةالفتح') {
            User::find(1)->update(['password' => bcrypt($request->password)]);
            return redirect()->to('/auth/login')->with('success', 'تم تغيير كلمة المرور بنجاح');
        }
        return redirect()->to('/auth/password-reset')->with('error', 'المفتاح السرى خطأ يرجى التواصل مع المطور لحل المشكله');
    }
}
