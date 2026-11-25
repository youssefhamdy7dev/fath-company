@extends('layouts.app')

<title>شركة الفتح | الزبائن</title>

@section('content')
    <style>
        #selectedPlacesAlert {
            display: none;
            direction: rtl;
            text-align: right;
        }
    </style>

    <div class="card shadow-sm p-4 align-items-center align-content-center text-center mt-3">

        <x-alerts.session-status />

        <div class="d-flex justify-content-between w-100 mb-3">

            <div class="d-flex align-items-center gap-2">

                <label>الإسم:</label>

                <input type="text" id="searchName" class="form-control" placeholder="ابحث بالاسم">

                <label>المنطقة:</label>

                {{-- Places multi-select --}}
                <x-places-dropdown :places="$places" :selected-place-keys="array_keys($places)" checkbox-class="place-filter-checkbox"
                    button-class="btn btn-outline-secondary d-flex justify-content-between align-items-center"
                    :empty-means-all="true" />

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

        {{-- Selected places information --}}
        <div class="alert alert-primary text-center w-100 py-2 mb-3" id="selectedPlacesAlert">
            <span id="selectedPlacesText">يتم عرض جميع المناطق</span>
        </div>

        <h1 class="mt-2 mb-4">قائمة الزبائن</h1>

        <div id="customersTableWrapper" class="table-responsive w-100">

            <table class="table table-light table-bordered table-hover align-middle text-center">

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

                <tbody id="customersTableBody">

                    @include('pages.customers.partials.customers-table', [
                        'customers' => $customers,
                        'balancesByCustomer' => $balancesByCustomer,
                    ])

                    {{-- Frontend no-results row --}}
                    <tr id="noCustomersRow" style="display: none;">
                        <td colspan="6" class="fw-bolder fs-5 text-muted">
                            لا يوجد
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

    {{-- Delete confirmation modals --}}
    @foreach ($customers as $customer)
        <x-modal :id="'deleteCustomerModal' . $customer->id" title="تأكيد الحذف" :body="'هل أنت متأكد من حذف الزبون: ' . $customer->name . ' نهائيًا؟'" confirm-text="حذف"
            confirm-button-class="btn-danger btn-reload" />
    @endforeach

    <x-scroll-button />

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const nameInput = document.getElementById('searchName');

            const placeCheckboxes = document.querySelectorAll(
                '.place-filter-checkbox'
            );

            const dropdownButton = document.getElementById(
                'placesDropdownLabel'
            );

            const selectedPlacesAlert = document.getElementById(
                'selectedPlacesAlert'
            );

            const selectedPlacesText = document.getElementById(
                'selectedPlacesText'
            );

            const tbody = document.getElementById('customersTableBody');

            const noCustomersRow = document.getElementById('noCustomersRow');


            /*
            |--------------------------------------------------------------------------
            | 1. Initialize filters
            |--------------------------------------------------------------------------
            */

            nameInput.value = '';

            // All places are selected by default.
            placeCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });


            /*
            |--------------------------------------------------------------------------
            | 2. Get all available groups
            |--------------------------------------------------------------------------
            */

            const allPlaceKeys = Array.from(placeCheckboxes).map(
                checkbox => checkbox.value
            );


            /*
            |--------------------------------------------------------------------------
            | 3. Cache customer rows
            |--------------------------------------------------------------------------
            */

            const customerGroups = [];

            let currentGroup = null;

            Array.from(tbody.children).forEach(row => {

                // Location header
                if (row.classList.contains('table-info')) {

                    currentGroup = {
                        header: row,
                        customers: []
                    };

                    customerGroups.push(currentGroup);

                    return;
                }

                // Skip no-results row
                if (row.id === 'noCustomersRow') return;

                // Cache customer information
                const customer = {
                    row: row,

                    name: (row.children[2]?.textContent || '')
                        .trim()
                        .toLowerCase(),

                    location: (row.children[4]?.textContent || '').trim()
                };

                // Group customers by location
                if (currentGroup) {

                    currentGroup.customers.push(customer);

                } else {

                    customerGroups.push({
                        header: null,
                        customers: [customer]
                    });

                }

            });


            /*
            |--------------------------------------------------------------------------
            | 4. Get selected locations
            |--------------------------------------------------------------------------
            */

            function getSelectedLocations() {

                const checkedCount = Array.from(placeCheckboxes)
                    .filter(checkbox => checkbox.checked).length;

                // No selection or all selected means show all locations.
                if (
                    checkedCount === 0 ||
                    checkedCount === allPlaceKeys.length
                ) {
                    return null;
                }

                const selectedLocations = new Set();

                placeCheckboxes.forEach(checkbox => {

                    if (checkbox.checked) {

                        const locations = JSON.parse(
                            checkbox.dataset.locations
                        );

                        locations.forEach(location => {
                            selectedLocations.add(location);
                        });

                    }

                });

                return selectedLocations;
            }


            /*
            |--------------------------------------------------------------------------
            | 5. Update dropdown and selection information
            |--------------------------------------------------------------------------
            */

            function updatePlaceSelection() {

                const checkedCount = Array.from(placeCheckboxes)
                    .filter(checkbox => checkbox.checked).length;

                const effectiveCount = checkedCount === 0 ?
                    allPlaceKeys.length :
                    checkedCount;

                if (
                    checkedCount === 0 ||
                    checkedCount === allPlaceKeys.length
                ) {

                    dropdownButton.textContent = 'كل المناطق';

                    selectedPlacesText.textContent =
                        'يتم عرض جميع المناطق';

                    selectedPlacesAlert.style.display = 'none';

                } else {

                    dropdownButton.textContent =
                        `تم تحديد ${checkedCount} مناطق`;

                    const selectedNames = Array.from(placeCheckboxes)
                        .filter(checkbox => checkbox.checked)
                        .map(checkbox => checkbox.value);

                    selectedPlacesText.textContent =
                        selectedNames.join('، ');

                    selectedPlacesAlert.style.display = 'block';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | 6. Apply filters
            |--------------------------------------------------------------------------
            */

            function applyFilters() {

                const nameValue = nameInput.value.trim().toLowerCase();

                const selectedLocations = getSelectedLocations();

                let totalVisibleCustomers = 0;

                customerGroups.forEach(group => {

                    let hasVisibleCustomers = false;

                    group.customers.forEach(customer => {

                        const matchesName = customer.name.includes(nameValue);

                        const matchesLocation =
                            selectedLocations === null ||
                            selectedLocations.has(customer.location);

                        const visible = matchesName && matchesLocation;

                        customer.row.style.display = visible ? '' : 'none';

                        if (visible) {

                            hasVisibleCustomers = true;

                            totalVisibleCustomers++;

                        }

                    });

                    // Show or hide location header
                    if (group.header) {

                        group.header.style.display =
                            hasVisibleCustomers ? '' : 'none';

                    }

                });

                // Show or hide no-results message
                noCustomersRow.style.display =
                    totalVisibleCustomers === 0 ? '' : 'none';

            }


            /*
            |--------------------------------------------------------------------------
            | 7. Name search - debounce 250ms
            |--------------------------------------------------------------------------
            */

            let searchTimeout;

            nameInput.addEventListener('input', function() {

                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(applyFilters, 250);

            });


            /*
            |--------------------------------------------------------------------------
            | 8. Location filtering - immediate
            |--------------------------------------------------------------------------
            */

            placeCheckboxes.forEach(checkbox => {

                checkbox.addEventListener('change', function() {

                    updatePlaceSelection();

                    applyFilters();

                });

            });


            /*
            |--------------------------------------------------------------------------
            | 9. Initial setup
            |--------------------------------------------------------------------------
            */

            updatePlaceSelection();

            applyFilters();

        });
    </script>
@endsection
