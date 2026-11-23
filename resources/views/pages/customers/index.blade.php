@extends('layouts.app')

<title>شركة الفتح | الزبائن</title>

@section('content')
    <style>
        /* Places dropdown - matching Payments by Date */
        #placesDropdownWrapper {
            position: relative;
            width: 300px;
        }

        #filterLocationDropdown {
            width: 100%;
            text-align: right;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #placesDropdownMenu {
            width: 100%;
            min-width: 300px;
            overflow-y: auto;
            text-align: right;
            direction: rtl;
            padding: 1rem;
        }

        #placesDropdownMenu .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-right: 0;
            margin-bottom: 0.5rem;
        }

        #placesDropdownMenu .form-check-input {
            float: none;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
        }

        #placesDropdownMenu .form-check-label {
            cursor: pointer;
            flex: 1;
        }

        #selectedPlacesAlert {
            display: none;
            direction: rtl;
            text-align: right;
        }

        @media (max-width: 576px) {
            #placesDropdownWrapper {
                width: 220px;
            }

            #placesDropdownMenu {
                min-width: 220px;
            }
        }
    </style>

    <div class="card shadow-sm p-4 align-items-center align-content-center text-center mt-3">

        <x-alerts.session-status />

        <div class="d-flex justify-content-between w-100 mb-3">

            <div class="d-flex align-items-center gap-2">

                <label>الإسم:</label>

                <input type="text" id="searchName" class="form-control" placeholder="ابحث بالاسم">

                <label>المنطقة:</label>

                {{-- Places multi-select - matching Payments by Date --}}
                <div class="dropdown" id="placesDropdownWrapper">

                    <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button"
                        id="filterLocationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        كل المناطق
                    </button>

                    <div class="dropdown-menu shadow" id="placesDropdownMenu" aria-labelledby="filterLocationDropdown">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <strong>اختر المناطق</strong>

                            <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearPlaceSelection">
                                إلغاء التحديد
                            </button>

                        </div>

                        <hr class="my-2">

                        @foreach ($places as $placeName => $placeLocations)
                            <div class="form-check">

                                <input class="form-check-input place-filter-checkbox" type="checkbox"
                                    value="{{ $placeName }}" id="placeFilter{{ $loop->index }}"
                                    data-locations='@json($placeLocations)' checked>

                                <label class="form-check-label" for="placeFilter{{ $loop->index }}">
                                    {{ $placeName }}
                                </label>

                            </div>
                        @endforeach

                        <hr class="my-2">

                        <div class="d-flex justify-content-between">

                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPlaces">
                                تحديد الكل
                            </button>

                            <small class="text-muted align-self-center" id="placesSelectionCount">
                                كل المناطق
                            </small>

                        </div>

                    </div>
                </div>

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
                'filterLocationDropdown'
            );

            const clearButton = document.getElementById(
                'clearPlaceSelection'
            );

            const selectAllButton = document.getElementById(
                'selectAllPlaces'
            );

            const selectionCount = document.getElementById(
                'placesSelectionCount'
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

                    selectionCount.textContent = 'كل المناطق';

                    selectedPlacesText.textContent =
                        'يتم عرض جميع المناطق';

                    selectedPlacesAlert.style.display = 'none';

                } else {

                    dropdownButton.textContent =
                        `تم تحديد ${checkedCount} مناطق`;

                    selectionCount.textContent =
                        `${checkedCount} من ${allPlaceKeys.length}`;

                    const selectedNames = Array.from(placeCheckboxes)
                        .filter(checkbox => checkbox.checked)
                        .map(checkbox => checkbox.value);

                    selectedPlacesText.textContent =
                        selectedNames.join('، ');

                    selectedPlacesAlert.style.display = 'block';

                }

                if (checkedCount === 0) {

                    selectionCount.textContent =
                        `كل المناطق (${effectiveCount})`;

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
            | 9. Clear selected places
            |--------------------------------------------------------------------------
            */

            clearButton.addEventListener('click', function(event) {

                event.preventDefault();

                placeCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                updatePlaceSelection();

                applyFilters();

            });


            /*
            |--------------------------------------------------------------------------
            | 10. Select all places
            |--------------------------------------------------------------------------
            */

            selectAllButton.addEventListener('click', function(event) {

                event.preventDefault();

                placeCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });

                updatePlaceSelection();

                applyFilters();

            });


            /*
            |--------------------------------------------------------------------------
            | 11. Initial setup
            |--------------------------------------------------------------------------
            */

            updatePlaceSelection();

            applyFilters();

        });
    </script>
@endsection
