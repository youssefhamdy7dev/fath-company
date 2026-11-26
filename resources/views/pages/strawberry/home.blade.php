@extends('layouts.app')

@section('content')
    <div class="card shadow-lg p-4">

        <h3 class="text-center mb-5">
            مرحبًا بك في <strong class="brand" style="font-size: 2rem">شركة الفتح</strong> لتجارة الفواكه
        </h3>

        <h4 class="text-center mb-4">إدارة <span class="badge bg-danger fs-5">الفراولة</span>
        </h4>

        <div class="row g-4 gap-4 justify-content-center" dir="rtl">
            <x-dashboard-card title="العمومى للفراولة" icon="bi-clipboard-data-fill" color="dark" :link="route('strawberry-customer-payments.dashboard')" />

            <x-dashboard-card title="عربات الفراولة" icon="bi-truck-front-fill" color="primary" :link="route('strawberry-trucks.index')" />

            <x-dashboard-card title="تحصيل الفراولة" icon="bi-cash-stack" color="success" :link="route('strawberry-customer-payments.index')" />

        </div>
    </div>
@endsection
