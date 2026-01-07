@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 text-center">

        {{-- Back to index --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('fruits.index') }}" color="info">
                العودة إلى قائمة الأصناف <i class="bi bi-boxes" style="font-size: 1.1rem;"></i>
            </x-button>
        </div>

        <h1 class="mb-4">تفاصيل الصنف</h1>

        <div class="card card-background w-75 mx-auto">

            {{-- Fruit Name --}}
            <div class="mb-3">
                <h4>إسم الصنف:</h4>
                <p class="fs-5">{{ $fruit->name }}</p>
            </div>

            {{-- Fruit Image --}}
            <div class="mb-3">
                <h4>الصورة الحالية:</h4>
                @if ($fruit->image && Storage::disk('public')->exists('fruits/' . $fruit->image))
                    <img src="{{ asset('storage/fruits/' . $fruit->image) }}" alt="{{ $fruit->name }}"
                        class="img-thumbnail" style="max-width: 300px;">
                @else
                    <p class="text-muted">لم يتم إرفاق صورة</p>
                @endif
            </div>

        </div>

        {{-- Edit + Delete --}}
        <div class="d-flex justify-content-center gap-2 mt-4">
            {{-- Edit --}}
            <x-button href="{{ route('fruits.edit', $fruit->id) }}" color="warning">
                تعديل <i class="bi bi-pencil-square"></i>
            </x-button>

            {{-- Delete triggers modal --}}
            <x-button type="button" color="danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                حذف <i class="bi bi-trash"></i>
            </x-button>
        </div>

        {{-- Delete Form --}}
        <form id="deleteForm" action="{{ route('fruits.destroy', $fruit->id) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>

    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal id="deleteModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا الصنف نهائيًا؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-reload btn-danger" />

    {{-- Modal JS --}}
    <script>
        document.getElementById('deleteModalConfirm').addEventListener('click', function() {
            document.getElementById('deleteForm').submit();
        });
    </script>
@endsection
