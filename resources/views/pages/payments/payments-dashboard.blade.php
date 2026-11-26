@extends('layouts.app')

@section('content')
    <style>
        /* Keep the filters above the tables and other page elements */
        .payments-filters-card {
            position: relative;
            z-index: 1060;
            overflow: visible !important;
        }

        .payments-filters-card .card-body,
        .payments-filters-card .row,
        .payments-filters-card .col-md-4 {
            overflow: visible !important;
        }

        /* Places dropdown */
        #placesDropdownWrapper {
            position: relative;
            z-index: 1061;
            width: 100%;
        }

        #placesDropdownMenu {
            z-index: 2000 !important;
            width: 100%;
            min-width: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            margin-top: 5px;
            text-align: right;
            direction: rtl;
        }

        #placesDropdownMenu .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            border-radius: 5px;
        }

        #placesDropdownMenu .form-check:hover {
            background-color: #f1f3f5;
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
            margin: 0;
        }

        #placesDropdownButton {
            min-height: 38px;
            white-space: normal;
        }

        #selectedPlacesAlert {
            display: none;
        }

        /* Daily totals */
        .daily-total-card {
            border-radius: 10px;
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

    <div class="container-fluid py-4" dir="rtl">

        {{-- Page Heading --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">صفحة العمومى</h3>
        </div>

        <x-alerts.session-status />

        {{-- Filters --}}
        <div class="card shadow-sm mb-4 payments-filters-card">
            <div class="card-body">

                <form id="paymentsFiltersForm" action="{{ route('payments-dashboard') }}" method="GET">

                    <div class="row align-items-end g-3">

                        {{-- Places Checklist Dropdown --}}
                        <div class="col-md-4">

                            <label class="form-label">
                                اختر المناطق
                            </label>

                            <x-places-dropdown :places="$places" :selected-place-keys="$selectedPlaceKeys" checkbox-name="places[]"
                                checkbox-class="place-checkbox"
                                button-class="btn btn-outline-secondary dropdown-toggle w-100 text-end d-flex justify-content-between align-items-center"
                                :empty-means-all="false" />

                        </div>

                        {{-- Customer Name Search --}}
                        <div class="col-md-4">

                            <label for="searchCustomer" class="form-label">
                                اسم الزبون
                            </label>

                            <input type="text" id="searchCustomer" class="form-control" placeholder="ابحث باسم الزبون..."
                                autocomplete="off">

                        </div>

                        {{-- Date --}}
                        <div class="col-md-2">

                            <label for="date" class="form-label">
                                التاريخ
                            </label>

                            <input type="text" id="date"
                                class="form-control globdatepicker @error('date') is-invalid @enderror" name="date"
                                value="{{ $displayDate }}" placeholder="اختر التاريخ" autocomplete="off">

                        </div>

                        {{-- Apply Filters --}}
                        <div class="col-md-2">

                            <button type="submit" id="applyFiltersButton" class="btn btn-primary w-100">

                                <i class="bi bi-funnel"></i>
                                تطبيق الفلاتر

                            </button>

                        </div>

                    </div>

                </form>

            </div>
        </div>

        {{-- Tables --}}
        <div id="paymentsTables">

            @include('pages.payments.partials.tables')

        </div>

        {{-- Selected Places Information --}}
        <div class="alert alert-success text-center mb-3" id="selectedPlacesAlert">

            <div id="selectedPlacesNames" class="fw-bold">
                كل المناطق
            </div>

        </div>

        {{-- Daily Totals --}}
        <div class="row g-3 mt-3" id="paymentsDailyTotals">

            {{-- Total Before Collection --}}
            <div class="col-md-3">

                <div class="card shadow-sm border-dark daily-total-card">

                    <div class="card-body text-center">

                        <h5 class="fw-bold mb-3">
                            إجمالى ما قبل التحصيل
                        </h5>

                        <h3 id="totalBeforeCollection" class="fw-bolder mb-0">
                            0
                        </h3>

                    </div>

                </div>

            </div>

            {{-- Total Payments --}}
            <div class="col-md-3">

                <div class="card shadow-sm border-success daily-total-card">

                    <div class="card-body text-center">

                        <h5 class="text-success fw-bold mb-3">
                            إجمالى التحصيل
                        </h5>

                        <h3 id="totalDailyPayments" class="text-success fw-bolder mb-0">
                            0
                        </h3>

                    </div>

                </div>

            </div>

            {{-- Total Discounts --}}
            <div class="col-md-3">

                <div class="card shadow-sm border-warning daily-total-card">

                    <div class="card-body text-center">

                        <h5 class="text-warning fw-bold mb-3">
                            إجمالى الخصومات
                        </h5>

                        <h3 id="totalDailyDiscounts" class="text-warning fw-bolder mb-0">
                            0
                        </h3>

                    </div>
                </div>

            </div>

            {{-- Total Remaining --}}
            <div class="col-md-3">

                <div class="card shadow-sm border-danger daily-total-card">

                    <div class="card-body text-center">

                        <h5 class="text-danger fw-bold mb-3">
                            إجمالى المتبقى
                        </h5>

                        <h3 id="totalDailyRemaining" class="text-danger fw-bolder mb-0">
                            0
                        </h3>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <x-scroll-button />

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==========================================
            // ELEMENTS
            // ==========================================

            const form = document.getElementById('paymentsFiltersForm');
            const container = document.getElementById('paymentsTables');
            const button = document.getElementById('applyFiltersButton');
            const nameInput = document.getElementById('searchCustomer');

            const dropdownLabel = document.getElementById('placesDropdownLabel');

            const placeCheckboxes = document.querySelectorAll('.place-checkbox');

            const placesSelectionCount = document.getElementById('placesSelectionCount');

            const selectedPlacesAlert = document.getElementById('selectedPlacesAlert');
            const selectedPlacesNames = document.getElementById('selectedPlacesNames');

            const allPlaceKeys = Array.from(placeCheckboxes).map(
                checkbox => checkbox.value
            );


            // ==========================================
            // 1. PLACES CHECKLIST + SELECTED NAMES
            // ==========================================

            function updatePlacesLabel() {

                const selected = Array.from(placeCheckboxes)
                    .filter(checkbox => checkbox.checked);

                const checkedCount = selected.length;

                const effectiveCount = checkedCount === 0 ?
                    allPlaceKeys.length :
                    checkedCount;

                // All places selected or none selected = show all places
                if (checkedCount === 0 || checkedCount === allPlaceKeys.length) {

                    dropdownLabel.textContent = 'كل المناطق';

                    placesSelectionCount.textContent = 'كل المناطق';

                    selectedPlacesNames.textContent = 'يتم عرض جميع المناطق';

                    selectedPlacesAlert.style.display = 'none';

                } else {

                    dropdownLabel.textContent =
                        `تم تحديد ${checkedCount} مناطق`;

                    placesSelectionCount.textContent =
                        `${checkedCount} من ${allPlaceKeys.length}`;

                    const selectedNames = selected.map(checkbox => {

                        const label = document.querySelector(
                            `label[for="${checkbox.id}"]`
                        );

                        return label ?
                            label.textContent.trim() :
                            checkbox.value;

                    });

                    selectedPlacesNames.textContent = selectedNames.join('، ');

                    selectedPlacesAlert.style.display = 'block';

                }

                if (checkedCount === 0) {

                    placesSelectionCount.textContent =
                        `كل المناطق (${effectiveCount})`;

                }

            }


            // Update label when a checkbox changes
            placeCheckboxes.forEach(checkbox => {

                checkbox.addEventListener('change', updatePlacesLabel);

            });


            // Initial dropdown state
            updatePlacesLabel();


            // ==========================================
            // 2. NORMALIZE CUSTOMER NAME
            // ==========================================

            function normalize(text) {

                return String(text || '')
                    .trim()
                    .toLowerCase();

            }


            // ==========================================
            // 3. PARSE NUMBERS
            // Supports Arabic, Persian and English digits
            // ==========================================

            function parseAmount(value) {

                const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
                const persianDigits = '۰۱۲۳۴۵۶۷۸۹';

                let text = String(value || '').trim();

                text = text.replace(/[٠-٩]/g, digit =>
                    arabicDigits.indexOf(digit)
                );

                text = text.replace(/[۰-۹]/g, digit =>
                    persianDigits.indexOf(digit)
                );

                text = text
                    .replace(/[٬,]/g, '')
                    .replace(/[٫]/g, '.')
                    .replace(/[^\d.-]/g, '');

                const amount = parseFloat(text);

                return Number.isFinite(amount) ? amount : 0;

            }


            // ==========================================
            // 4. FILTER CUSTOMERS + CALCULATE TOTALS
            // ==========================================

            function applyCustomerFilterAndTotals() {

                const searchValue = normalize(nameInput.value);

                let totalBeforeCollection = 0;
                let totalPayments = 0;
                let totalDiscounts = 0;
                let totalRemaining = 0;

                const groupCards = container.querySelectorAll('[data-place-group]');

                groupCards.forEach(card => {

                    const customerRows = card.querySelectorAll(
                        'tr[data-customer-row]'
                    );

                    let visibleCustomers = 0;

                    customerRows.forEach(row => {

                        const customerName = normalize(
                            row.dataset.customerName
                        );

                        const matchesName = customerName.includes(searchValue);

                        // Show/hide customer
                        row.style.display = matchesName ? '' : 'none';

                        if (matchesName) {
                            visibleCustomers++;
                        }

                        // Calculate totals from ALL rows.
                        // Customer name search does NOT affect totals.
                        const cells = row.querySelectorAll('td');

                        // Column 1: حساب الزبون (before collection)
                        totalBeforeCollection += parseAmount(
                            cells[0]?.innerText
                        );

                        // Column 4: التحصيل
                        totalPayments += parseAmount(
                            cells[3]?.innerText
                        );

                        // Column 5: الخصم
                        totalDiscounts += parseAmount(
                            cells[4]?.innerText
                        );

                        // Column 5: ما تبقى بعد التحصيل
                        totalRemaining += parseAmount(
                            cells[5]?.innerText
                        );

                    });

                    // Hide the entire group if no customers match
                    card.style.display =
                        visibleCustomers > 0 || searchValue === '' ?
                        '' :
                        'none';

                });


                // ==========================================
                // 5. UPDATE TOTALS
                // ==========================================

                const formatter = new Intl.NumberFormat('ar-EG', {
                    maximumFractionDigits: 2
                });

                document.getElementById('totalBeforeCollection').textContent =
                    formatter.format(totalBeforeCollection);

                document.getElementById('totalDailyPayments').textContent =
                    formatter.format(totalPayments);

                document.getElementById('totalDailyDiscounts').textContent =
                    formatter.format(totalDiscounts);

                document.getElementById('totalDailyRemaining').textContent =
                    formatter.format(totalRemaining);

            }


            // ==========================================
            // 6. LIVE CUSTOMER SEARCH
            // ==========================================

            nameInput.addEventListener('input', function() {

                applyCustomerFilterAndTotals();

            });


            // ==========================================
            // 7. AJAX FILTERS (DATE + PLACES)
            // ==========================================

            form.addEventListener('submit', async function(event) {

                event.preventDefault();

                const params = new URLSearchParams(new FormData(form));

                button.disabled = true;
                container.style.opacity = '0.5';
                container.setAttribute('aria-busy', 'true');

                try {

                    const response = await fetch(
                        `${form.action}?${params.toString()}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        }
                    );

                    if (!response.ok) {
                        throw new Error('Failed to load payments tables.');
                    }

                    const html = await response.text();

                    // Replace tables only
                    container.innerHTML = html;

                    // Update browser URL
                    history.replaceState(
                        null,
                        '',
                        `${form.action}?${params.toString()}`
                    );

                    // Reapply customer search and recalculate all totals
                    applyCustomerFilterAndTotals();

                } catch (error) {

                    console.error(error);

                    alert('حدث خطأ أثناء تحديث التحصيلات. حاول مرة أخرى.');

                } finally {

                    button.disabled = false;
                    container.style.opacity = '1';
                    container.removeAttribute('aria-busy');

                }

            });


            // ==========================================
            // 8. INITIAL LOAD
            // ==========================================

            applyCustomerFilterAndTotals();

        });
    </script>
@endsection
