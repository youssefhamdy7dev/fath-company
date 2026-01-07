@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center">
        {{-- Back to index --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <button class="btn btn-info" onclick="location.href='{{ route('customers.index') }}'">
                العودة إلى قائمة الزبائن <i class="bi bi-person-lines-fill"></i>
            </button>
        </div>

        <h1 class="mb-3 text-center">إضافة زبون جديد</h1>

        <form method="POST" action="{{ route('customers.store') }}" dir="rtl" class="form-control" autocomplete="off">
            @csrf

            <div class="container w-50">

                {{-- اسم الزبون --}}
                <x-form.input id="name" label="إسم الزبون" name="name" value="{{ old('name') }}" />

                {{-- رقم الهاتف --}}
                <x-form.input id="phone" label="رقم الهاتف" name="phone" value="{{ old('phone') }}" />

                {{-- المنطقة --}}
                <x-form.select id="location" label="المنطقة" name="location" :options="$locations"
                    selected="{{ old('location') }}" />

                {{-- حساب الزبون --}}
                <x-form.input id="account" label="حساب الزبون" type="number" name="account" value="{{ old('account') }}"
                    placeholder="إجمالى حساب الزبون..." />

                {{-- ملاحظات --}}
                <x-form.input id="notes" label="ملاحظات" type="text" name="notes" value="{{ old('notes') }}" />

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-reload btn-primary w-50">
                        إضافة الزبون<i class="ps-1 bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
