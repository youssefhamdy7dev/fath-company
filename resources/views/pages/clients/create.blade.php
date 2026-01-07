@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('clients.index') }}" class="btn btn-info">
                العودة إلى قائمة العملاء <i class="bi bi-person-lines-fill"></i>
            </a>
        </div>

        <h1 class="mb-4 text-center">إضافة عميل جديد</h1>

        <form method="POST" action="{{ route('clients.store') }}" dir="rtl" class="form-control" autocomplete="off">
            @csrf
            <div class="container w-50">

                <x-form.input id="name" label="اسم العميل" name="name" value="{{ old('name') }}" />
                <x-form.input id="phone" label="رقم الهاتف" name="phone" value="{{ old('phone') }}" />

                <div class="text-center mt-4 mb-3">
                    <button type="submit" class="btn btn-reload btn-primary px-5">
                        إضافة العميل<i class="ps-1 bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
