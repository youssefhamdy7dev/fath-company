@extends('layouts.app')

@section('content')
    <div class="container" dir="rtl">
        <x-alerts.session-status />

        <div class="m-2 text-end">
            <a href="{{ route('customer-payments.index') }}" class="btn btn-dark">الرجوع للقائمة السابقة
                <i class="ps-1 bi bi-arrow-up-left"></i>
            </a>
        </div>
        <h3 class="text-center fw-bolder mb-3"> إضافة يومية تحصيل</h3>

        @if ($errors->has('duplicates'))
            <div class="alert alert-danger">{{ $errors->first('duplicates') }}</div>
        @endif

        @if ($errors->has('payments'))
            <div class="alert alert-danger">{{ $errors->first('payments') }}</div>
        @endif

        <form method="POST" action="{{ route('customer-payments.daily.store') }}" id="dailyPaymentsForm" autocomplete="off">
            @csrf
            <div class="mb-3 d-flex justify-content-center text-center gap-3">
                <div class="col-md-3">
                    <label for="date" class="form-label fw-bolder text-primary">تاريخ يومية التحصيل</label>
                    <input type="text" id="date"
                        class="form-control globdatepicker @error('date') is-invalid @enderror" name="date"
                        value="{{ old('date') }}">
                    @error('date')
                        <div class="invalid-feedback fw-bolder">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="form-label fw-bolder">المنطقة:</label>
                    <select id="location" class="form-select">
                        <option value="">الكل</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}">{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
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
                                <td colspan="6" class="fw-bold text-center">{{ $location }}</td>
                            </tr>

                            @foreach ($customersInLoc as $customer)
                                <tr>
                                    <td>{{ transform_numeric_value($loop->iteration) }}</td>
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
                    حفظ اليومية<i class="ps-1 bi bi-save"></i>
                </button>
            </div>
        </form>
    </div>

    <x-scroll-button />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const locationSelectInput = document.getElementById('location');
            const balanceCells = document.querySelectorAll('.prev-balance');

            dateInput.addEventListener('change', function() {
                let chosenDate = this.value;

                if (!chosenDate) return;

                fetch("{{ route('customer-payments.daily.balance') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            date: chosenDate
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;

                        let balances = data.balances;
                        let formatted = data.formatted;

                        // Update all balances per customer
                        document.querySelectorAll('tbody tr').forEach(row => {
                            let input = row.querySelector('input[name^="payments["]');
                            if (!input) return;

                            let customerId = input.value;
                            let cell = row.querySelector('.prev-balance');

                            if (balances[customerId] !== undefined) {
                                cell.textContent = balances[customerId];
                            }
                        });

                        // Update date column in the table
                        document.querySelectorAll('.selected-date-display').forEach(cell => {
                            cell.textContent = formatted;
                        });
                    });
            });
            locationSelectInput.addEventListener('change', function() {
                let selectedLocation = this.value;
                let tableRows = document.querySelectorAll('tbody tr');

                tableRows.forEach(row => {
                    // Check if the row is a location header
                    if (row.querySelector('td[colspan="6"]')) {
                        if (selectedLocation === "" || row.textContent.trim() ===
                            selectedLocation) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    } else {
                        // For customer rows, check the previous sibling for location
                        let prevRow = row.previousElementSibling;
                        while (prevRow && !prevRow.querySelector('td[colspan="6"]')) {
                            prevRow = prevRow.previousElementSibling;
                        }

                        if (prevRow) {
                            let location = prevRow.textContent.trim();
                            if (selectedLocation === "" || location === selectedLocation) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    }
                });
            });
        });
    </script>
@endsection
