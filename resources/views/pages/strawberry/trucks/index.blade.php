@extends('layouts.app')

<title>شركة الفتح | عربات الفراولة</title>

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">

        <x-alerts.session-status />

        <div class="d-flex justify-content-between w-100 mb-3">
            <a href="{{ route('strawberry') }}" class="btn btn-dark">
                الرئيسية <i class="ps-1 bi bi-house"></i>
            </a>

            <a href="{{ route('strawberry-trucks.create') }}" class="btn btn-success">
                إضافة عربة فراولة جديدة
                <i class="ps-1 bi bi-plus-square"></i>
            </a>
        </div>
        @include('pages.strawberry.trucks.partials.filters')

        <h1 class="mb-4">قائمة عربات <strong class="badge bg-danger">الفراولة</strong></h1>

        @include('pages.strawberry.trucks.partials.truck-list', [
            'heading' => 'العربات غير المكتملة',
            'headingClass' => 'text-danger',
            'trucks' => $unfinishedTrucks,
            'emptyMessage' => 'لا توجد عربات غير مكتملة.',
            'hasFilters' => $hasFilters,
        ])

        @include('pages.strawberry.trucks.partials.truck-list', [
            'heading' => 'العربات المكتملة',
            'headingClass' => 'text-success mt-5',
            'trucks' => $finishedTrucks,
            'emptyMessage' => 'لا توجد عربات مكتملة.',
            'hasFilters' => $hasFilters,
        ])

    </div>
@endsection
