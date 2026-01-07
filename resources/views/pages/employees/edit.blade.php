@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center">
        {{-- Back Button --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('employees.index') }}" class="btn btn-info">
                العودة إلى قائمة الموظفين <i class="bi bi-person-gear"></i>
            </a>
        </div>

        <h1 class="mb-4 text-center">تعديل بيانات الموظف</h1>

        <form method="POST" action="{{ route('employees.update', $employee->id) }}" dir="rtl" class="form-control"
            autocomplete="off">
            @csrf
            @method('PUT')

            <div class="container w-50">

                {{-- Name --}}
                <x-form.input id="name" label="إسم الموظف" name="name" value="{{ old('name', $employee->name) }}" />

                {{-- Payment --}}
                <x-form.input id="payment" label="اليومية" type="number" name="payment"
                    value="{{ old('payment', $employee->payment) }}" />

                <div class="d-flex justify-content-between align-items-start">
                    {{-- Remaining --}}
                    <x-form.input id="remaining_withdrawal" label="باقى حساب له" type="number" name="remaining_withdrawal"
                        value="{{ old('remaining_withdrawal', $employee->remaining_withdrawal) }}" />
                    {{-- Overlimit --}}
                    <x-form.input id="over_withdrawal_limit" label="باقى حساب عليه" type="number"
                        name="over_withdrawal_limit"
                        value="{{ old('over_withdrawal_limit', $employee->over_withdrawal_limit) }}" />
                </div>

                {{-- Start Date --}}
                <x-form.input id="start_date" label="تاريخ بدء العمل" type="text" name="start_date"
                    value="{{ old('start_date', $employee->start_date->format('Y-m-d')) }}"
                    placeholder="تاريخ بداية العمل ( أول يوم عمل )" class="datepicker" />

                {{-- Submit --}}
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-reload btn-primary px-5 w-50">
                        حفظ التعديلات <i class="bi bi-save"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
