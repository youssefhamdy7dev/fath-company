@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 mt-3">
        <x-alerts.session-status />

        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('trucks.index') }}" color="success">
                صرف فاتورة جديدة <i class="bi bi-receipt-cutoff"></i>
            </x-button>
        </div>

        <h1 class="mb-5 text-center">قائمة الفواتير</h1>

        <div class="d-flex justify-content-start align-items-center text-center mb-3">
            <label for="clientFilter" class="me-2 fw-bold">اختر العميل:</label>
            <select id="clientFilter" class="form-select w-auto">
                <option value="all">الكل</option>
                @foreach ($groupedBills as $group)
                    <option value="client-{{ $group['client']->id }}">{{ $group['client']->name }}</option>
                @endforeach
            </select>
        </div>

        @forelse ($groupedBills as $group)
            <div class="mb-5 border rounded p-3 client-group" id="client-{{ $group['client']->id }}">
                <h4 class="mb-3 text-primary">{{ $group['client']->name }}</h4>

                <table class="table table-secondary table-bordered table-hover align-middle text-center">
                    <thead>
                        <tr class="table-dark">
                            <th>#</th>
                            <th>تاريخ الفاتورة</th>
                            <th>تاريخ الصرف</th>
                            <th class="text-danger">المبلغ</th>
                            <th>الصنف / الأصناف</th>
                            <th>ملاحظات</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($group['bills'] as $bill)
                            <tr>
                                <td>{{ transform_numbers($loop->iteration) }}</td>
                                <td class="text-primary">{{ transform_numbers($bill->truck->date->format('d-m-Y')) }}</td>
                                <td class="text-danger">{{ transform_numbers($bill->billing_date->format('d-m-Y')) }}</td>
                                <td class="fw-bolder fs-5 text-danger">
                                    {{ transform_numeric_value(round_to_nearest_tenth($bill->bill_price)) }}</td>
                                <td>{{ $bill->truck->fruit_names }}</td>
                                <td>{{ $bill->notes ?? 'لا يوجد ملاحظات' }}</td>
                                <td>
                                    <a href="{{ route('bills.show', $bill) }}" class="btn btn-info btn-sm">عرض <i
                                            class="bi bi-eye-fill"></i></a>
                                    <a href="{{ route('bills.edit', $bill) }}" class="btn btn-warning btn-sm">تعديل<i
                                            class="bi bi-pencil-square ps-1"></i></a>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $bill->id }}"
                                        data-name="{{ transform_numbers($bill->billing_date->format('d-m-Y')) }}"
                                        data-bs-toggle="modal" data-bs-target="#deleteBillModal">
                                        حذف <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted text-center">لا توجد فواتير لهذا العميل</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Client-specific pagination --}}
                <div class="mt-3">
                    {{ $group['bills']->links() }}
                </div>
            </div>
        @empty
            <p class="text-center text-muted">لا توجد فواتير بعد.</p>
        @endforelse
    </div>

    {{-- Delete Modal --}}
    <x-modal id="deleteBillModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه الفاتورة؟"
        confirmButtonClass="btn-danger btn-reload" />

    <form id="deleteBillForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <script>
        const filterSelect = document.getElementById('clientFilter');
        const groups = document.querySelectorAll('.client-group');
        // Restore previous selection
        const saved = localStorage.getItem('selectedClient');
        if (saved) filterSelect.value = saved;
        const updateView = () => {
            const selected = filterSelect.value;
            localStorage.setItem('selectedClient', selected);
            groups.forEach(group => {
                if (selected === 'all' || group.id === selected) {
                    group.style.display = 'block';
                } else {
                    group.style.display = 'none';
                }
            });
        };
        filterSelect.addEventListener('change', updateView);
        // Run on page load
        updateView();
        // Clear saved selection on unload
        window.addEventListener('beforeunload', () => {
            localStorage.removeItem('selectedClient');
        });

        // Delete button handler
        document.addEventListener('DOMContentLoaded', () => initDeleteHandler('bill'));
    </script>
@endsection
