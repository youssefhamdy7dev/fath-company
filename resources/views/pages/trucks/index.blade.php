@extends('layouts.app')

<title>شركة الفتح | العربات</title>

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">

        {{-- Alerts --}}
        <x-alerts.session-status />

        {{-- Top buttons --}}
        <div class="d-flex justify-content-between w-100 mb-3">
            <a href="{{ route('customers.reports') }}" class="btn btn-dark">
                كشف مشتروات الزبائن <i class="ps-1 bi bi-cart4"></i>
            </a>

            <div>
                <a href="{{ route('bills.index') }}" class="btn btn-outline-danger">
                    إدارة الفواتير <i class="ps-1 bi bi-receipt-cutoff"></i>
                </a>

                <a href="{{ route('trucks.create') }}" class="btn btn-success">
                    إضافة عربة جديدة <i class="ps-1 bi bi-plus-square"></i>
                </a>
            </div>
        </div>

        {{-- Truck filters --}}
        @include('pages.trucks.partials.filters')

        <h1 class="mb-4">قائمة العربات</h1>

        {{-- Unfinished trucks --}}
        @include('pages.trucks.partials.truck-list', [
            'heading' => 'العربات الغير مكتملة',
            'headingClass' => 'text-danger',
            'trucks' => $unfinishedTrucks,
            'emptyMessage' => 'لا توجد عربات غير مكتملة.',
            'hasFilters' => $hasFilters,
        ])

        {{-- Finished trucks --}}
        @include('pages.trucks.partials.truck-list', [
            'heading' => 'العربات المكتملة',
            'headingClass' => 'text-success mt-5',
            'trucks' => $finishedTrucks,
            'emptyMessage' => 'لا توجد عربات مكتملة.',
            'hasFilters' => $hasFilters,
        ])

    </div>
@endsection
