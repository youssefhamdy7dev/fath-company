@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center mt-3">
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('drivers.index') }}" class="btn btn-info">
                العودة إلى قائمة السائقون <i class="ps-1 bi bi-person-video2"></i>
            </a>
        </div>

        <h1 class="mb-4 text-center">تعديل بيانات السائق</h1>

        <form method="POST" action="{{ route('drivers.update', $driver->id) }}" dir="rtl" class="form-control w-50"
            autocomplete="off">
            @csrf
            @method('PUT')

            <x-form.input id="name" label="إسم السائق" name="name" value="{{ old('name', $driver->name) }}" />
            <x-form.input id="phone" label="رقم الهاتف" name="phone" value="{{ old('phone', $driver->phone) }}" />

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-reload btn-primary w-50">
                    حفظ التعديلات <i class="bi bi-save"></i>
                </button>
            </div>
        </form>
    </div>
@endsection
