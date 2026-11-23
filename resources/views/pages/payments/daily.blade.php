@extends('layouts.app')

@section('content')
    @php
        $places = [
            'صقر والبساتين' => ['ميدان صقر', 'الكويتية', 'صقر', 'العمومى بساتين', 'البساتين', 'سوق البساتين'],
            'السد العالى' => ['السد العالى', 'ميدان أبو عبده', 'على عبدالعزيز', 'أبو بريك'],
            'الجزيرة ودار السلام' => ['المطبعة', 'الجزيرة', 'دار السلام'],
            'فايدة كامل وحسنين الدسوقى' => [
                'البير',
                'المشير وأبو الوفا',
                'عبدالحميد مكى',
                'فايدة كامل',
                'حسنين الدسوقى',
                'المعادى',
            ],
            'أخرى' => ['أخرى'],
        ];
    @endphp

    <div class="container" dir="rtl">
        <x-alerts.session-status />

        <div class="m-2 text-end">
            <a href="{{ route('customer-payments.index') }}" class="btn btn-dark">
                الرجوع للقائمة السابقة
                <i class="ps-1 bi bi-arrow-up-left"></i>
            </a>
        </div>

        <h3 class="text-center fw-bolder mb-3">إضافة يومية تحصيل</h3>

        @if ($errors->has('duplicates'))
            <div class="alert alert-danger">{{ $errors->first('duplicates') }}</div>
        @endif

        @if ($errors->has('payments'))
            <div class="alert alert-danger">{{ $errors->first('payments') }}</div>
        @endif

        <form method="POST" action="{{ route('customer-payments.daily.store') }}" id="dailyPaymentsForm" autocomplete="off">

            @csrf

            <div class="mb-3 d-flex justify-content-center align-items-end text-center gap-3 flex-wrap">

                {{-- Date --}}
                <div class="col-md-3">
                    <label for="date" class="form-label fw-bolder text-primary">
                        تاريخ يومية التحصيل
                    </label>

                    <input type="text" id="date"
                        class="form-control globdatepicker @error('date') is-invalid @enderror" name="date"
                        value="{{ old('date') }}">

                    @error('date')
                        <div class="invalid-feedback fw-bolder">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Places filter --}}
                <div class="text-center">
                    <label class="form-label fw-bolder d-block">المنطقة:</label>

                    <div class="dropdown" id="placesDropdownWrapper">
                        <button class="btn btn-outline-primary dropdown-toggle w-100" type="button"
                            id="filterLocationDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span id="selectedLocationsCount">كل المناطق</span>
                        </button>

                        <div class="dropdown-menu p-3 shadow" id="placesDropdownMenu"
                            aria-labelledby="filterLocationDropdown">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>اختر المناطق</strong>

                                <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearLocations">
                                    إلغاء التحديد
                                </button>
                            </div>

                            <hr class="my-2">

                            @foreach ($places as $placeName => $placeLocations)
                                <div class="form-check mb-2">
                                    <input class="form-check-input location-checkbox" type="checkbox"
                                        value="{{ $placeName }}" data-locations='@json($placeLocations)'
                                        id="placeFilter{{ $loop->index }}">

                                    <label class="form-check-label" for="placeFilter{{ $loop->index }}">
                                        {{ $placeName }}
                                    </label>
                                </div>
                            @endforeach

                            <hr class="my-2">

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllLocations">
                                    تحديد الكل
                                </button>

                                <small class="text-muted align-self-center" id="locationsSelectionCount">
                                    كل المناطق
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Selected places display --}}
            <div id="selectedLocationsDisplay" class="alert alert-info text-center py-2 mb-3" style="display: none;">

                <strong>المناطق المختارة:</strong>
                <span id="selectedLocationsNames"></span>
            </div>

            <div class="table-responsive">
                <table class="table table-secondary table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>حساب الزبون</th>
                            <th>الزبون</th>
                            <th>المبلغ</th>
                            <th>الخصم</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($customers as $location => $customersInLoc)
                            <tr class="table-primary">
                                <td colspan="6" class="fw-bold text-center">
                                    {{ $location }}
                                </td>
                            </tr>

                            @foreach ($customersInLoc as $customer)
                                <tr>
                                    <td>
                                        {{ transform_numeric_value($loop->iteration) }}
                                    </td>

                                    <td class="prev-balance">
                                        -
                                    </td>

                                    <td>
                                        <input type="hidden" name="payments[{{ $customer->id }}][customer_id]"
                                            value="{{ $customer->id }}">

                                        {{ $customer->name }}
                                    </td>

                                    <td>
                                        <input type="number" name="payments[{{ $customer->id }}][amount]" min="1"
                                            class="form-control" value="{{ old("payments.$customer->id.amount") }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01" name="payments[{{ $customer->id }}][discount]"
                                            class="form-control" value="{{ old("payments.$customer->id.discount") }}">
                                    </td>

                                    <td class="selected-date-display">
                                        -
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-reload btn-outline-success">
                    حفظ اليومية
                    <i class="ps-1 bi bi-save"></i>
                </button>
            </div>
        </form>
    </div>

    <x-scroll-button />

    <style>
        #placesDropdownWrapper {
            position: relative;
            width: 300px;
        }

        #placesDropdownMenu {
            width: 100%;
            min-width: 300px;
            max-height: 350px;
            overflow-y: auto;
            text-align: right;
            z-index: 1055;
        }

        #placesDropdownMenu .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-right: 0;
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

        #placesDropdownMenu .form-check:hover {
            background-color: rgba(0, 0, 0, 0.04);
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const tbody = document.querySelector('.table tbody');

            // Places filter elements
            const locationCheckboxes = document.querySelectorAll('.location-checkbox');
            const selectedLocationsCount = document.getElementById('selectedLocationsCount');
            const locationsSelectionCount = document.getElementById('locationsSelectionCount');
            const clearLocationsButton = document.getElementById('clearLocations');
            const selectAllLocationsButton = document.getElementById('selectAllLocations');
            const selectedLocationsDisplay = document.getElementById('selectedLocationsDisplay');
            const selectedLocationsNames = document.getElementById('selectedLocationsNames');

            const allPlaceNames = Array.from(locationCheckboxes).map(checkbox => checkbox.value);

            // Cache customer groups and their DOM elements
            const customerGroups = [];
            let currentGroup = null;

            Array.from(tbody.children).forEach(row => {

                // Location header
                if (row.classList.contains('table-primary')) {
                    currentGroup = {
                        header: row,
                        location: row.textContent.trim(),
                        customers: []
                    };

                    customerGroups.push(currentGroup);
                    return;
                }

                const customerInput = row.querySelector('input[name^="payments["]');

                if (!customerInput) return;

                const customer = {
                    id: customerInput.value,
                    row: row,
                    balanceCell: row.querySelector('.prev-balance'),
                    dateCell: row.querySelector('.selected-date-display')
                };

                if (currentGroup) {
                    currentGroup.customers.push(customer);
                }
            });

            // Cache all customer elements for date updates
            const allCustomers = customerGroups.flatMap(group => group.customers);

            // Apply places filtering and update dropdown information
            function applyLocationFilter() {
                const selectedCheckboxes = Array.from(locationCheckboxes)
                    .filter(checkbox => checkbox.checked);

                const showAll = selectedCheckboxes.length === 0;

                // Get all individual locations belonging to selected groups
                const selectedLocations = selectedCheckboxes.flatMap(checkbox => {
                    return JSON.parse(checkbox.dataset.locations);
                });

                customerGroups.forEach(group => {
                    const matchesLocation = showAll ||
                        selectedLocations.includes(group.location);

                    group.header.style.display = matchesLocation ? '' : 'none';

                    group.customers.forEach(customer => {
                        customer.row.style.display = matchesLocation ? '' : 'none';
                    });
                });

                // Update dropdown label and selection count
                if (showAll || selectedCheckboxes.length === locationCheckboxes.length) {
                    selectedLocationsCount.textContent = 'كل المناطق';
                    locationsSelectionCount.textContent = 'كل المناطق';

                    selectedLocationsDisplay.style.display = 'none';
                } else {
                    const selectedPlaceNames = selectedCheckboxes.map(checkbox => checkbox.value);

                    selectedLocationsCount.textContent =
                        `تم تحديد ${selectedPlaceNames.length} مناطق`;

                    locationsSelectionCount.textContent =
                        `${selectedPlaceNames.length} من ${allPlaceNames.length}`;

                    selectedLocationsNames.textContent = selectedPlaceNames.join('، ');
                    selectedLocationsDisplay.style.display = '';
                }
            }

            // Location checkbox changes
            locationCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', applyLocationFilter);
            });

            // Clear all selected places
            clearLocationsButton.addEventListener('click', function() {
                locationCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                applyLocationFilter();
            });

            // Select all places
            selectAllLocationsButton.addEventListener('click', function() {
                locationCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });

                applyLocationFilter();
            });

            // Initialize dropdown information and filtering
            applyLocationFilter();

            // Date balance requests
            let balanceController = null;

            dateInput.addEventListener('change', async function() {
                const chosenDate = this.value;

                if (!chosenDate) return;

                // Cancel previous request
                if (balanceController) {
                    balanceController.abort();
                }

                balanceController = new AbortController();

                try {
                    const response = await fetch(
                        "{{ route('customer-payments.daily.balance') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                date: chosenDate
                            }),
                            signal: balanceController.signal
                        }
                    );

                    if (!response.ok) {
                        throw new Error('Failed to retrieve customer balances.');
                    }

                    const data = await response.json();

                    if (!data.success) return;

                    // Update customer balances
                    allCustomers.forEach(customer => {
                        if (
                            Object.prototype.hasOwnProperty.call(
                                data.balances,
                                customer.id
                            )
                        ) {
                            customer.balanceCell.textContent =
                                data.balances[customer.id];
                        }
                    });

                    // Update displayed date
                    allCustomers.forEach(customer => {
                        customer.dateCell.textContent = data.formatted;
                    });

                } catch (error) {
                    if (error.name === 'AbortError') return;

                    console.error('Balance loading error:', error);
                }
            });
        });
    </script>
@endsection
