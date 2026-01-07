@extends('layouts.app')

@section('content')
    @php
        $formatted = $customer->created_at->translatedFormat('التاريخ: ( l ، j F Y ) - الوقت: ( h:i A )');
        $formatted = str_replace(['AM', 'PM'], ['ص', 'م'], $formatted);
    @endphp

    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">

        {{-- Back to index button --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('customers.index') }}" class="btn btn-info">
                العودة إلى قائمة الزبائن <i class="bi bi-person-lines-fill"></i>
            </a>
        </div>

        <h1 class="mb-4">بيانات الزبون</h1>

        {{-- Customer Information --}}
        <div class="w-75 mx-auto">
            <div class="card p-4 card-background">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 class="text-muted mb-1">إسم الزبون:</h5>
                        <p class="fs-5 fw-semibold">{{ $customer->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-muted mb-1">رقم الهاتف:</h5>
                        <p class="{{ $customer->phone ? 'fs-5 fw-semibold' : 'text-muted' }}">
                            {{ $customer->phone ?? 'لم يتم تسجيل رقم هاتف' }}
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 class="text-muted mb-1">المنطقة:</h5>
                        <p class="fs-5 fw-semibold">{{ $customer->location }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-muted mb-1">حساب الزبون:</h5>
                        <p class="fs-5 fw-semibold text-primary">{{ transform_numeric_value($customer->current_balance) }}
                        </p>
                    </div>
                </div>

                <div class="mt-3">
                    <h6 class="text-muted mb-2">تاريخ الإضافة:</h6>
                    <p class="text-dark">{{ transform_numbers($formatted) }}</p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex justify-content-center gap-2 mt-4">
            {{-- Purchases --}}
            <a href="{{ route('customers.purchases', $customer->id) }}" class="btn btn-primary px-4">
                سجل المشتروات والتحصيلات <i class="bi bi-cart4"></i>
            </a>

            {{-- Payments --}}
            {{-- <a href="{{ route('customers.payments', $customer->id) }}" class="btn btn-dark px-4">
                سجل التحصيل <i class="bi bi-list-columns"></i>
            </a> --}}

            {{-- Edit --}}
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning px-4">
                تعديل <i class="bi bi-pencil-square"></i>
            </a>

            {{-- Delete triggers modal --}}
            <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#deleteModal">
                حذف <i class="bi bi-trash"></i>
            </button>
        </div>

        {{-- Delete Form --}}
        <form id="deleteForm" action="{{ route('customers.destroy', $customer->id) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </div>

    {{-- Modal Component --}}
    <x-modal id="deleteModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا الزبون نهائيًا؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />

    {{-- JS to handle modal confirm --}}
    <script>
        document.getElementById('deleteModalConfirm').addEventListener('click', function() {
            document.getElementById('deleteForm').submit();
        });
    </script>
@endsection
