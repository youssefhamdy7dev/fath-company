@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center mt-3">
        <x-alerts.session-status />

        <div class="d-flex justify-content-between w-100 mb-3">
            <div class="d-flex align-items-center gap-2">
                <label>الإسم:</label>
                <input type="text" id="searchName" class="form-control" placeholder="ابحث بالاسم">
                <label>المنطقة: </label>
                <select id="filterLocation" class="form-select">
                    <option value="">الكل</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location }}">{{ $location }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <a href="{{ route('customers.reports') }}" class="btn btn-dark">
                    كشف مشتروات الزبائن<i class="ps-1 bi bi-cart4"></i>
                </a>
                <x-button href="{{ route('customers.create') }}" color="success">
                    إضافة زبون جديد <i class="bi bi-person-fill-add"></i>
                </x-button>
            </div>
        </div>

        <h1 class="mt-2 mb-4">قائمة الزبائن</h1>

        <div id="customersTableWrapper" class="table-responsive w-100">
            <table class="table table-light table-bordered table-hover align-middle">
                <thead>
                    <tr class="table-dark">
                        <th>#</th>
                        <th>إجمالى حساب الزبون</th>
                        <th>الإسم</th>
                        <th>رقم الهاتف</th>
                        <th>المنطقة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                {{-- <tbody id="customersTableBody">
                    @include('pages.customers.partials.customers-table', ['customers' => $customers])
                </tbody> --}}
                <tbody id="customersTableBody">
                    @include('pages.customers.partials.customers-table', ['customers' => $customers])

                    {{-- Frontend "no results" row --}}
                    <tr id="noCustomersRow" style="display: none;">
                        <td colspan="6" class="fw-bolder fs-5 text-muted">لا يوجد</td>
                    </tr>
                </tbody>
            </table>
            {{-- <div class="d-flex justify-content-center mt-3">
                {{ $customers->links() }}
            </div> --}}
        </div>
    </div>

    @foreach ($customers as $customer)
        <x-modal :id="'deleteCustomerModal' . $customer->id" title="تأكيد الحذف" :body="'هل أنت متأكد من حذف الزبون: ' . $customer->name . ' نهائيًا؟'" confirm-text="حذف"
            confirm-button-class="btn-danger btn-reload" />
    @endforeach

    <x-scroll-button />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('searchName');
            const locationSelect = document.getElementById('filterLocation');
            const rows = document.querySelectorAll('#customersTableBody tr');
            // Restore saved filters
            const savedName = localStorage.getItem('customer_filter_name') || '';
            const savedLocation = localStorage.getItem('customer_filter_location') || '';
            nameInput.value = savedName;
            locationSelect.value = savedLocation;

            function normalize(text) {
                return text.trim().toLowerCase();
            }

            function applyFilters() {
                const nameValue = normalize(nameInput.value);
                const locationValue = locationSelect.value;

                localStorage.setItem('customer_filter_name', nameInput.value);
                localStorage.setItem('customer_filter_location', locationSelect.value);

                let currentLocationHeader = null;
                let hasVisibleRowsInLocation = false;
                let totalVisibleCustomers = 0;

                rows.forEach(row => {
                    // Location header row
                    if (row.classList.contains('table-info')) {
                        if (currentLocationHeader && !hasVisibleRowsInLocation) {
                            currentLocationHeader.style.display = 'none';
                        }

                        currentLocationHeader = row;
                        hasVisibleRowsInLocation = false;
                        row.style.display = '';
                        return;
                    }

                    // Skip "no customers" row
                    if (row.id === 'noCustomersRow') return;

                    const nameCell = row.children[2]?.innerText || '';
                    const locationCell = row.children[4]?.innerText || '';

                    const matchName = normalize(nameCell).includes(nameValue);
                    const matchLocation = !locationValue || locationCell === locationValue;

                    if (matchName && matchLocation) {
                        row.style.display = '';
                        hasVisibleRowsInLocation = true;
                        totalVisibleCustomers++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Hide last location header if empty
                if (currentLocationHeader && !hasVisibleRowsInLocation) {
                    currentLocationHeader.style.display = 'none';
                }

                // Toggle "no results" row
                document.getElementById('noCustomersRow').style.display =
                    totalVisibleCustomers === 0 ? '' : 'none';
            }
            nameInput.addEventListener('input', applyFilters);
            locationSelect.addEventListener('change', applyFilters);
            // Apply filters on load
            applyFilters();
            window.addEventListener('beforeunload', () => {
                localStorage.removeItem('customer_filter_name');
                localStorage.removeItem('customer_filter_location');
            });
        });
    </script>
@endsection
