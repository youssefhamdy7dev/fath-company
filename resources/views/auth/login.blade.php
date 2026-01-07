@extends('layouts.app')

@section('content')
    {{-- ✅ Unified Session Alerts --}}
    <x-alerts.session-status />

    {{-- ✅ Login Card --}}
    <x-card.form-container title="تسجيل الدخول">
        <form method="POST" action="{{ route('auth.login') }}" dir="rtl" autocomplete="off">
            @csrf

            {{-- Username --}}
            <x-form.input id="username" name="username" label="إسم المستخدم" type="text" autofocus />

            {{-- Password --}}
            <x-form.input id="password" name="password" label="كلمة المرور" type="password" />

            {{-- Remember Me --}}
            <x-form.checkbox id="remember" name="remember" label="تذكرني" />

            {{-- Submit --}}
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary">
                    تسجيل الدخول
                    <i class="bi bi-box-arrow-in-left ms-1"></i>
                </button>
            </div>

            {{-- Forgot Password --}}
            <div class="text-center mt-3">
                <a href="{{ route('password') }}" class="text-decoration-none text-secondary">
                    نسيت كلمة المرور؟
                </a>
            </div>
        </form>
    </x-card.form-container>
@endsection
