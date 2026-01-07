@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center">
        {{-- Back Button --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('employees.index') }}" class="btn btn-info">
                العودة إلى قائمة الموظفين <i class="bi bi-person-gear"></i>
            </a>
        </div>

        <h1 class="mb-4 text-center">إضافة موظف جديد</h1>

        <form method="POST" action="{{ route('employees.store') }}" dir="rtl" class="form-control" autocomplete="off">
            @csrf
            <div class="container w-50">

                {{-- Name --}}
                <x-form.input id="name" label="إسم الموظف" name="name" value="{{ old('name') }}" />

                {{-- Payment --}}
                <x-form.input id="payment" label="اليومية" type="number" name="payment" value="{{ old('payment') }}" />

                <div class="d-flex justify-content-between align-items-start">
                    {{-- Remaining --}}
                    <x-form.input id="remaining_withdrawal" label="باقى حساب له" type="number" name="remaining_withdrawal"
                        value="{{ old('remaining_withdrawal', 0) }}" />
                    {{-- Overlimit --}}
                    <x-form.input id="over_withdrawal_limit" label="باقى حساب عليه" type="number"
                        name="over_withdrawal_limit" value="{{ old('over_withdrawal_limit', 0) }}" />
                </div>

                {{-- Start Date --}}
                <x-form.input id="start_date" label="تاريخ بدء العمل" type="text" name="start_date"
                    value="{{ old('start_date') }}" placeholder="تاريخ بداية العمل ( أول يوم عمل )"
                    class="globdatepicker" />

                {{-- Submit --}}
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-reload btn-primary px-5 w-50">
                        إضافة الموظف<i class="ps-1 bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
