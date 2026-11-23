@extends('layouts.app')

@section('content')
    <div class="container" dir="rtl">
        <x-alerts.session-status />

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="mb-0">
                تحصيل تاريخ:
                {{ transform_numbers($displayDate->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
            </h3>

            <div>
                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addCustomerPaymentModal">
                    إضافة تحصيل زبون
                    <i class="ps-1 bi bi-plus-circle-fill"></i>
                </button>

                <a href="{{ route('customer-payments.index') }}" class="btn btn-dark">
                    العودة إلى القائمة السابقة
                    <i class="ps-1 bi bi-arrow-up-left"></i>
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="d-flex gap-3 mb-3 flex-wrap align-items-center">

            {{-- Name filter --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0">الإسم:</label>
                <input type="text" id="searchName" class="form-control" placeholder="ابحث بالاسم"
                    style="max-width:300px;">
            </div>

            {{-- Places multi-select --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0">المناطق:</label>

                <div class="dropdown" id="placesDropdownWrapper">
                    <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button"
                        id="filterLocationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        كل المناطق
                    </button>

                    <div class="dropdown-menu p-3 shadow" id="placesDropdownMenu" aria-labelledby="filterLocationDropdown">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>اختر المناطق</strong>

                            <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearPlaceSelection">
                                إلغاء التحديد
                            </button>
                        </div>

                        <hr class="my-2">

                        @foreach ($places as $placeName => $placeLocations)
                            <div class="form-check mb-2">
                                <input class="form-check-input place-filter-checkbox" type="checkbox"
                                    value="{{ $placeName }}" id="placeFilter{{ $loop->index }}" checked>

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
        </div>

        {{-- Selected places information --}}
        <div class="alert alert-primary text-center py-2 mb-3" id="selectedPlacesAlert">
            <span id="selectedPlacesText">يتم عرض جميع المناطق</span>
        </div>

        {{-- Payments table --}}
        <div id="paymentsByDateWrapper">
            @include('pages.payments.partials.payments-by-date-table', [
                'grouped' => $grouped,
                'balancesByCustomer' => $balancesByCustomer,
            ])
        </div>
    </div>

    {{-- Add Payment Modal --}}
    @include('pages.payments.partials.add-payment-modal', [
        'pageDate' => $displayDate,
    ])

    <x-scroll-button />

    <style>
        #placesDropdownWrapper {
            position: relative;
            width: 300px;
        }

        #placesDropdownMenu {
            width: 100%;
            min-width: 300px;
            overflow-y: auto;
            text-align: right;
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
        }

        #placesDropdownMenu .form-check-label {
            cursor: pointer;
            flex: 1;
        }

        #selectedPlacesAlert {
            display: none;
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

            const nameInput = document.getElementById('searchName');
            const placeCheckboxes = document.querySelectorAll('.place-filter-checkbox');

            const dropdownButton = document.getElementById('filterLocationDropdown');
            const clearButton = document.getElementById('clearPlaceSelection');
            const selectAllButton = document.getElementById('selectAllPlaces');

            const selectionCount = document.getElementById('placesSelectionCount');
            const selectedPlacesAlert = document.getElementById('selectedPlacesAlert');
            const selectedPlacesText = document.getElementById('selectedPlacesText');

            const wrapper = document.getElementById('paymentsByDateWrapper');

            let activeRequest = null;

            // Get all available group names from the checkboxes.
            const allPlaceKeys = Array.from(placeCheckboxes).map(
                checkbox => checkbox.value
            );

            // Return selected groups.
            // If none are selected, return ALL groups.
            function getSelectedPlaces() {
                const selected = Array.from(placeCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                return selected.length > 0 ? selected : allPlaceKeys;
            }

            // Update dropdown text and selection information.
            function updatePlaceSelection() {
                const checkedCount = Array.from(placeCheckboxes)
                    .filter(checkbox => checkbox.checked).length;

                const effectiveCount = checkedCount === 0 ?
                    allPlaceKeys.length :
                    checkedCount;

                if (checkedCount === 0 || checkedCount === allPlaceKeys.length) {
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

            // Handle delete buttons after table refresh.
            function wireDeleteButtons() {
                if (typeof initDeleteHandler === 'function') {
                    initDeleteHandler('payment');
                }

                const deleteButtons = wrapper.querySelectorAll('.delete-btn');
                const deleteForm = document.getElementById('deletePaymentForm');

                if (!deleteForm) return;

                deleteButtons.forEach(button => {
                    if (button.dataset.deleteActionBound === 'true') return;

                    button.dataset.deleteActionBound = 'true';

                    button.addEventListener('click', function() {
                        deleteForm.action = this.dataset.url;
                    });
                });
            }

            // Fetch filtered table.
            async function fetchFilteredTable() {
                if (!wrapper) return;

                if (activeRequest) {
                    activeRequest.abort();
                }

                const controller = new AbortController();
                activeRequest = controller;

                const params = new URLSearchParams();

                if (nameInput.value.trim()) {
                    params.append('name', nameInput.value.trim());
                }

                // Always send at least all groups.
                // This ensures that unchecking everything shows all places.
                params.append('places_filter', '1');

                getSelectedPlaces().forEach(place => {
                    params.append('places[]', place);
                });

                try {
                    const response = await fetch(
                        `{{ route('customer-payments.byDate', ['date' => $carbonDate]) }}?${params.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            signal: controller.signal
                        }
                    );

                    if (!response.ok) {
                        throw new Error(`HTTP error: ${response.status}`);
                    }

                    const html = await response.text();

                    wrapper.innerHTML = html;

                    wireDeleteButtons();

                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Error loading payments:', error);
                    }
                } finally {
                    if (activeRequest === controller) {
                        activeRequest = null;
                    }
                }
            }

            // Events
            nameInput.addEventListener('input', fetchFilteredTable);

            placeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updatePlaceSelection();
                    fetchFilteredTable();
                });
            });

            clearButton.addEventListener('click', function(event) {
                event.preventDefault();

                placeCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                updatePlaceSelection();
                fetchFilteredTable();
            });

            selectAllButton.addEventListener('click', function(event) {
                event.preventDefault();

                placeCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });

                updatePlaceSelection();
                fetchFilteredTable();
            });

            // Initial setup
            updatePlaceSelection();
            wireDeleteButtons();

        });
    </script>
@endsection
