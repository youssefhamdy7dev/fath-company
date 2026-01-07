@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center">

        {{-- Back to index --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('customers.index') }}" class="btn btn-info">
                العودة إلى قائمة الزبائن
                <i class="bi bi-person-lines-fill"></i>
            </a>
        </div>

        <h1 class="mb-4 text-center">تعديل بيانات الزبون</h1>

        <form method="POST" action="{{ route('customers.update', $customer->id) }}" dir="rtl" class="form-control"
            autocomplete="off">
            @csrf
            @method('PUT')

            <div class="container w-50">

                {{-- اسم الزبون --}}
                <x-form.input id="name" label="إسم الزبون" name="name" value="{{ old('name', $customer->name) }}" />

                {{-- رقم الهاتف --}}
                <x-form.input id="phone" label="رقم الهاتف" name="phone"
                    value="{{ old('phone', $customer->phone) }}" />

                {{-- المنطقة --}}
                <x-form.select id="location" label="المنطقة" name="location" :options="$locations"
                    selected="{{ old('location', $customer->location) }}" />

                {{-- حساب الزبون --}}
                <x-form.input id="account" label="حساب الزبون" type="number" name="account"
                    value="{{ old('account', $customer->account ?? 0) }}">
                </x-form.input>
                <div class="form-text text-muted mb-3">حساب الزبون اذا كان يوجد له حساب من قبل</div>

                {{-- ملاحظات --}}
                <x-form.input id="notes" label="ملاحظات" type="text" name="notes"
                    value="{{ old('notes', $customer->notes) }}" />

                {{-- Save Button --}}
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-reload btn-primary px-5 w-50">
                        حفظ التعديلات <i class="bi bi-save"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>
@endsection
