@extends('layouts.app')

@section('content')
    <div class="card shadow-lg p-4">
        <h3 class="text-center mb-5">
            مرحبًا بك في <strong class="brand" style="font-size: 2rem">شركة الفتح</strong> لتجارة الفواكه
        </h3>

        <div class="row g-4 gap-4 justify-content-center" dir="rtl">
            {{-- Dashboard Sections --}}
            <x-dashboard-card title="الأصناف" icon="bi-box-seam-fill" color="info" :link="route('fruits.index')" />

            <x-dashboard-card title="العملاء" icon="bi-person-badge-fill" color="warning" :link="route('clients.index')" />

            <x-dashboard-card title="السائقون" icon="bi-person-video2" color="secondary" :link="route('drivers.index')" />

            <x-dashboard-card title="الموظفون" icon="bi-person-gear" color="dark" :link="route('employees.index')" />

            <x-dashboard-card title="الزبائن" icon="bi-people-fill" color="primary" :link="route('customers.index')" />

            <x-dashboard-card title="التحصيل" icon="bi-cash-stack" color="success" :link="route('customer-payments.index')" />

            <x-dashboard-card title="الفراولة" icon="bi-basket2" color="danger" :link="route('home')" />

            <x-dashboard-card title="العربات" icon="bi-truck-front-fill" color="success" :link="route('trucks.index')" />

            <x-dashboard-card title="الفواتير" icon="bi-receipt-cutoff" color="danger" :link="route('bills.index')" />
        </div>
    </div>
@endsection
