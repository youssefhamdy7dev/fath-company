@extends('layouts.app')

@section('content')
    @php
        $createdAt = str_replace(
            ['AM', 'PM'],
            ['ص', 'م'],
            $employee->created_at->translatedFormat('التاريخ: ( l ، j F Y ) - الوقت: ( h:i A )'),
        );

        $formattedStartDate = $employee->start_date->translatedFormat('l, j F Y');
    @endphp
    {{-- Session alerts --}}
    <x-alerts.session-status />
    {{-- ================= EMPLOYEE CARD ================= --}}
    <div class="card shadow-sm p-4 text-center">

        {{-- Back --}}
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('employees.index') }}" class="btn btn-info">
                العودة إلى قائمة الموظفين <i class="bi bi-person-gear"></i>
            </a>
        </div>

        <h1 class="mb-4">بيانات الموظف</h1>

        <div class="w-75 mx-auto">
            <div class="card p-4 card-background">
                <div class="row text-center">

                    <h5 class="text-muted">الاسم</h5>
                    <p class="fs-5 fw-semibold">{{ $employee->name }}</p>

                    <h5 class="text-muted">
                        تاريخ أول يوم عمل ( {{ transform_numbers($employee->start_date->format('d-m-Y')) }} )
                    </h5>
                    <p class="fs-5 fw-semibold">{{ $formattedStartDate }}</p>

                    <h5 class="text-muted">اليومية</h5>
                    <p class="fs-5 fw-semibold text-primary">
                        {{ transform_numeric_value($employee->payment) }}
                    </p>

                    @if ($employee->remaining_withdrawal > 0)
                        <h5 class="text-muted">له (قديم)</h5>
                        <p class="fs-5 fw-semibold text-success">
                            {{ transform_numeric_value($employee->remaining_withdrawal) }}
                        </p>
                    @elseif ($employee->over_withdrawal_limit > 0)
                        <h5 class="text-muted">عليه (قديم)</h5>
                        <p class="fs-5 fw-semibold text-danger">
                            {{ transform_numeric_value($employee->over_withdrawal_limit) }}
                        </p>
                    @endif

                    <h5 class="text-muted">تاريخ التسجيل على النظام</h5>
                    <p class="fs-5 fw-semibold">
                        {{ transform_numbers($createdAt) }}
                    </p>

                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-between gap-2 mt-4">
                <button class="btn btn-dark px-4" data-bs-toggle="modal" data-bs-target="#monthlyWageModal">
                    تصفية الحساب <i class="bi bi-cash"></i>
                </button>

                <div>
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning px-4">
                        تعديل <i class="bi bi-pencil-square"></i>
                    </a>
                    <button class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        حذف <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Delete form --}}
        <form id="deleteForm" action="{{ route('employees.destroy', $employee->id) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </div>

    {{-- Delete Modal --}}
    <x-modal id="deleteModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا الزبون نهائيًا؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />

    {{-- ================= TABS ================= --}}
    <div class="section-separator my-4"></div>

    <div class="tabs-container mb-4">
        <div class="tabs">
            <div class="tab active" data-target="withdrawals">السحوبات</div>
            <div class="tab" data-target="holidays">الأجازات</div>
        </div>
    </div>

    <div id="withdrawals-section">
        @include('pages.employees.partials.withdrawals')
    </div>

    <div id="holidays-section" class="d-none">
        @include('pages.employees.partials.holidays')
    </div>

    {{-- ================= WAGE HISTORY ================= --}}
    <div class="section-separator my-4"></div>
    @include('pages.employees.partials.wage-history')

    {{-- ================= MONTHLY WAGE MODAL ================= --}}
    @include('pages.employees.partials.monthly-wage-modal')
@endsection

<x-scroll-button />

@push('scripts')
    <script>
        document.getElementById('deleteModalConfirm')?.addEventListener('click', () => {
            document.getElementById('deleteForm').submit();
        });

        const tabs = document.querySelectorAll('.tab');
        const sections = {
            withdrawals: document.getElementById('withdrawals-section'),
            holidays: document.getElementById('holidays-section'),
        };

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                Object.values(sections).forEach(s => s.classList.add('d-none'));
                sections[tab.dataset.target].classList.remove('d-none');
            });
        });
    </script>

    @if (session('show_wage_modal'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                new bootstrap.Modal(document.getElementById('monthlyWageModal')).show();
            });
        </script>
    @endif
@endpush
