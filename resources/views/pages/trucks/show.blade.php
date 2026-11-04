@extends('layouts.app')

<title>عربة: {{ $truck->fruit_names }}</title>

@section('content')
    {{-- 🔹 Top Section: Truck Main Info --}}
    @include('pages.trucks.partials.truck-main-info')

    {{-- Action buttons --}}
    <div class="d-flex justify-content-center gap-2 mb-3">
        <a href="{{ route('trucks.edit', $truck->id) }}" class="btn btn-warning btn-sm">
            تعديل <i class="bi bi-pencil-square"></i>
        </a>

        {{-- delete button — wired to the reusable modal system --}}
        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $truck->id }}"
            data-name="عربة بتاريخ {{ transform_numbers(\Carbon\Carbon::parse($truck->date)->format('d-m-Y')) }}"
            data-bs-toggle="modal" data-bs-target="#deleteTruckModal">
            حذف<i class="bi bi-trash"></i>
        </button>
    </div>

    {{-- 🔹 Bottom Section: Placeholder --}}
    @include('pages.trucks.partials.truck-purchases-table')

    {{-- 🔹 Truck Details Modal --}}
    @include('pages.trucks.partials.truck-details-modal')

    {{-- 🔹 Add Customers Form Modal --}}
    @include('pages.trucks.partials.add-customer-purchase')

    {{-- Reusable modal component — used across the app (matches x-modal signature in project) --}}
    <x-modal id="deleteTruckModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه العربة؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />

    {{-- Hidden delete form the JS will update the action on open --}}
    <form id="deleteTruckForm" method="POST" class="d-none">
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
