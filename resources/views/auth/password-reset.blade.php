@extends('layouts.app')

@section('content')
    {{-- ✅ Unified Session Alerts --}}
    <x-alerts.session-status />

    {{-- ✅ Password Reset Form --}}
    <x-card.form-container title="إسترجاع كلمة المرور">
        <form method="POST" action="{{ route('password.reset') }}" dir="rtl" autocomplete="off">
            @csrf

            {{-- Secret Key --}}
            <x-form.input id="secretKey" name="secretKey" label="المفتاح السري" type="text" autofocus />

            {{-- New Password --}}
            <x-form.input id="password" name="password" label="كلمة المرور الجديدة" type="password" />

            {{-- Confirm Password --}}
            <x-form.input id="password_confirmation" name="password_confirmation" label="تأكيد كلمة المرور"
                type="password" />

            {{-- Submit --}}
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary">
                    إعادة ضبط كلمة السر
                    <i class="bi bi-wrench-adjustable-circle ms-1"></i>
                </button>
            </div>

            {{-- Back to Login --}}
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-decoration-none text-secondary">
                    تسجيل الدخول
                </a>
            </div>
        </form>
    </x-card.form-container>
@endsection
