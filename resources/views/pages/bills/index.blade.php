@extends('layouts.app')

<title>شركة الفتح | الفواتير</title>

@section('content')
    <div class="card shadow-sm p-4 mt-3">
        <x-alerts.session-status />

        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('trucks.index') }}" color="success">
                صرف فاتورة جديدة <i class="bi bi-receipt-cutoff"></i>
            </x-button>
        </div>

        <h1 class="mb-4 text-center">قائمة الفواتير</h1>

        @include('pages.bills.partials.filters')

        <div id="billsGroups">
            @forelse ($groupedBills as $group)
                @include('pages.bills.partials.client-group', [
                    'group' => $group,
                ])
            @empty
                <p class="text-center text-muted">لا توجد فواتير بعد.</p>
            @endforelse
        </div>

        <p id="noBillsFound" class="text-center text-muted d-none">
            لا توجد فواتير مطابقة للبحث.
        </p>
    </div>

    <x-modal id="deleteBillModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه الفاتورة؟"
        confirmButtonClass="btn-danger btn-reload" />

    <form id="deleteBillForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    @include('pages.bills.partials.index-scripts')
@endsection
