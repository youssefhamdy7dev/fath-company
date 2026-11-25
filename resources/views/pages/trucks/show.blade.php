@extends('layouts.app')

<title>عربة: {{ $truck->fruit_names }}</title>

@section('content')
    {{-- Truck main information --}}
    @include('pages.trucks.partials.truck-main-info')

    {{-- Truck actions --}}
    <div class="d-flex justify-content-center gap-2 mb-3">
        <a href="{{ route('trucks.edit', $truck->id) }}" class="btn btn-warning btn-sm">
            تعديل <i class="bi bi-pencil-square"></i>
        </a>

        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $truck->id }}"
            data-name="عربة بتاريخ {{ transform_numbers(\Carbon\Carbon::parse($truck->date)->format('d-m-Y')) }}"
            data-bs-toggle="modal" data-bs-target="#deleteTruckModal">
            حذف <i class="bi bi-trash"></i>
        </button>
    </div>

    {{-- Customer purchases and distribution --}}
    @include('pages.trucks.partials.truck-purchases-table')

    {{-- Truck details --}}
    @include('pages.trucks.partials.truck-details-modal')

    {{-- Add customer purchases --}}
    @include('pages.trucks.partials.add-customer-purchase')

    {{-- Truck deletion confirmation --}}
    <x-modal id="deleteTruckModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه العربة؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />

    {{-- Hidden delete form --}}
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
