@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center">
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('clients.index') }}" class="btn btn-info">
                العودة إلى قائمة العملاء <i class="bi bi-person-lines-fill"></i>
            </a>
        </div>

        <h1 class="mb-4 text-center">تعديل بيانات العميل</h1>

        <form method="POST" action="{{ route('clients.update', $client->id) }}" dir="rtl"
            class="form-control w-50 mx-auto" autocomplete="off">
            @csrf
            @method('PUT')

            <x-form.input id="name" label="اسم العميل" name="name" value="{{ old('name', $client->name) }}" />
            <x-form.input id="phone" label="رقم الهاتف" name="phone" value="{{ old('phone', $client->phone) }}" />

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-reload btn-primary w-50">
                    حفظ التعديلات <i class="bi bi-save"></i>
                </button>
            </div>
        </form>
    </div>
@endsection
