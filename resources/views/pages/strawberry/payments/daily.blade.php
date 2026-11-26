@extends('layouts.app')

@section('content')
    <div class="container" dir="rtl">
        <x-alerts.session-status />

        <div class="m-2 text-end">
            <a href="{{ route('strawberry-customer-payments.index') }}" class="btn btn-dark">
                الرجوع للقائمة السابقة
                <i class="ps-1 bi bi-arrow-up-left"></i>
            </a>
        </div>

        <h3 class="text-center fw-bolder mb-3">إضافة يومية تحصيل الفراولة</h3>

        @if ($errors->has('duplicates'))
            <div class="alert alert-danger">{{ $errors->first('duplicates') }}</div>
        @endif

        @if ($errors->has('payments'))
            <div class="alert alert-danger">{{ $errors->first('payments') }}</div>
        @endif

        <form method="POST" action="{{ route('strawberry-customer-payments.daily.store') }}"
            id="strawberryDailyPaymentsForm" autocomplete="off">

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

                    <x-places-dropdown :places="$places" checkbox-class="strawberry-location-checkbox"
                        button-class="btn btn-outline-primary" menu-max-height="350px" :empty-means-all="true" />
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
                            <th>رصيد الفراولة السابق</th>
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
        .places-dropdown-menu {
            z-index: 1055;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const tbody = document.querySelector('#strawberryDailyPaymentsForm .table tbody');

            const locationCheckboxes = document.querySelectorAll('.strawberry-location-checkbox');
            const selectedLocationsCount = document.getElementById('placesDropdownLabel');
            const locationsSelectionCount = document.getElementById('placesSelectionCount');
            const selectedLocationsDisplay = document.getElementById('selectedLocationsDisplay');
            const selectedLocationsNames = document.getElementById('selectedLocationsNames');

            const allPlaceNames = Array.from(locationCheckboxes).map(
                checkbox => checkbox.value
            );

            const customerGroups = [];
            let currentGroup = null;

            Array.from(tbody.children).forEach(row => {
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

            const allCustomers = customerGroups.flatMap(group => group.customers);

            function applyLocationFilter() {
                const selectedCheckboxes = Array.from(locationCheckboxes)
                    .filter(checkbox => checkbox.checked);

                const showAll = selectedCheckboxes.length === 0;

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

                if (showAll || selectedCheckboxes.length === locationCheckboxes.length) {
                    selectedLocationsCount.textContent = 'كل المناطق';
                    locationsSelectionCount.textContent = 'كل المناطق';

                    selectedLocationsDisplay.style.display = 'none';
                } else {
                    const selectedPlaceNames = selectedCheckboxes.map(
                        checkbox => checkbox.value
                    );

                    selectedLocationsCount.textContent =
                        `تم تحديد ${selectedPlaceNames.length} مناطق`;

                    locationsSelectionCount.textContent =
                        `${selectedPlaceNames.length} من ${allPlaceNames.length}`;

                    selectedLocationsNames.textContent = selectedPlaceNames.join('، ');
                    selectedLocationsDisplay.style.display = '';
                }
            }

            locationCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', applyLocationFilter);
            });

            applyLocationFilter();

            // Date balance requests
            let balanceController = null;

            dateInput.addEventListener('change', async function() {
                const chosenDate = this.value;

                if (!chosenDate) return;

                if (balanceController) {
                    balanceController.abort();
                }

                balanceController = new AbortController();

                try {
                    const response = await fetch(
                        "{{ route('strawberry-customer-payments.daily.balance') }}", {
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
                        throw new Error('Failed to retrieve Strawberry balances.');
                    }

                    const data = await response.json();

                    if (!data.success) return;

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

                    allCustomers.forEach(customer => {
                        customer.dateCell.textContent = data.formatted;
                    });

                } catch (error) {
                    if (error.name === 'AbortError') return;

                    console.error('Strawberry balance loading error:', error);
                }
            });
        });
    </script>
@endsection
