@extends('layouts.app')

<title>عربة فراولة: {{ $truck->fruit_names }}</title>

@section('content')
    <x-alerts.session-status />

    @include('pages.strawberry.trucks.partials.truck-main-info')

    <div class="d-flex justify-content-center gap-2 mb-3">
        <a href="{{ route('strawberry-trucks.edit', $truck->id) }}" class="btn btn-warning btn-sm">
            تعديل <i class="bi bi-pencil-square"></i>
        </a>

        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $truck->id }}"
            data-name="عربة فراولة بتاريخ {{ $truck->date->format('d-m-Y') }}" data-bs-toggle="modal"
            data-bs-target="#deleteTruckModal">
            حذف <i class="bi bi-trash"></i>
        </button>
    </div>

    @include('pages.strawberry.trucks.partials.truck-purchases-table')

    @include('pages.strawberry.trucks.partials.truck-details-modal')

    @include('pages.strawberry.trucks.partials.add-customer-purchase')

    <x-modal id="deleteTruckModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه العربة؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />

    <form id="deleteTruckForm" method="POST" action="{{ route('strawberry-trucks.destroy', $truck->id) }}" class="d-none">

        @csrf
        @method('DELETE')
    </form>

    <x-scroll-button />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteHandler('truck');
        });
    </script>
@endsection
