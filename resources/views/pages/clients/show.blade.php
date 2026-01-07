@extends('layouts.app')

@section('content')
    @php
        $formatted = $client->created_at->translatedFormat('التاريخ: ( l ، j F Y ) - الوقت: ( h:i A )');
        $formatted = str_replace(['AM', 'PM'], ['ص', 'م'], $formatted);
    @endphp
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('clients.index') }}" class="btn btn-info">
                العودة إلى قائمة العملاء <i class="bi bi-person-lines-fill"></i>
            </a>
        </div>

        <h1 class="mb-4">بيانات العميل</h1>

        <div class="w-75 mx-auto">
            <div class="card p-4 card-background">
                <h5 class="text-muted mb-1">الاسم:</h5>
                <p class="fs-5 fw-semibold">{{ $client->name }}</p>

                <h5 class="text-muted mb-1">رقم الهاتف:</h5>
                <p class="fs-5 fw-semibold">{{ $client->phone ?? 'لم يتم تسجيل رقم هاتف' }}</p>

                <h6 class="text-muted mb-2 mt-3">تاريخ الإضافة:</h6>
                <p class="text-dark">{{ transform_numbers($formatted) }}</p>
                </p>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-2 mt-4">
            <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning px-4">تعديل <i
                    class="bi bi-pencil-square"></i></a>
            <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#deleteModal">حذف<i
                    class="bi bi-trash"></i></button>
        </div>

        <form id="deleteForm" action="{{ route('clients.destroy', $client->id) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <x-modal id="deleteModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا العميل نهائيًا؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />

    <script>
        document.getElementById('deleteModalConfirm').addEventListener('click', function() {
            document.getElementById('deleteForm').submit();
        });
    </script>
@endsection
