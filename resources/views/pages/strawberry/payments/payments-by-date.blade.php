@extends('layouts.app')

@section('content')
    <div class="container" dir="rtl">
        <x-alerts.session-status />

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="mb-0">
                تحصيل <span class="badge bg-danger">فراولة</span> بتاريخ:
                {{ transform_numbers($displayDate->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
            </h3>

            <div>
                <button class="btn btn-outline-success" data-bs-toggle="modal"
                    data-bs-target="#addStrawberryCustomerPaymentModal">
                    إضافة تحصيل زبون
                    <i class="ps-1 bi bi-plus-circle-fill"></i>
                </button>

                <a href="{{ route('strawberry-customer-payments.index') }}" class="btn btn-dark">
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

                <x-places-dropdown :places="$places" :selected-place-keys="array_keys($places)" checkbox-class="strawberry-place-filter-checkbox"
                    button-class="btn btn-outline-secondary dropdown-toggle w-100" :empty-means-all="true" />
            </div>
        </div>

        {{-- Selected places information --}}
        <div class="alert alert-primary text-center py-2 mb-3" id="selectedPlacesAlert">
            <span id="selectedPlacesText">يتم عرض جميع المناطق</span>
        </div>

        {{-- Payments table --}}
        <div id="strawberryPaymentsByDateWrapper">
            @include('pages.strawberry.payments.partials.payments-by-date-table', [
                'grouped' => $grouped,
                'customers' => $customers,
                'balancesByCustomer' => $balancesByCustomer,
            ])
        </div>
    </div>

    {{-- Add Payment Modal --}}
    @include('pages.strawberry.payments.partials.add-payment-modal', [
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
            const placeCheckboxes = document.querySelectorAll('.strawberry-place-filter-checkbox');

            const selectedPlacesAlert = document.getElementById('selectedPlacesAlert');
            const selectedPlacesText = document.getElementById('selectedPlacesText');
            const wrapper = document.getElementById('strawberryPaymentsByDateWrapper');

            let activeRequest = null;

            const allPlaceKeys = Array.from(placeCheckboxes).map(
                checkbox => checkbox.value
            );

            function getSelectedPlaces() {
                const selected = Array.from(placeCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                return selected.length > 0 ? selected : allPlaceKeys;
            }

            function updatePlaceSelection() {
                const checkedCount = Array.from(placeCheckboxes)
                    .filter(checkbox => checkbox.checked).length;

                if (checkedCount === 0 || checkedCount === allPlaceKeys.length) {
                    selectedPlacesText.textContent = 'يتم عرض جميع المناطق';
                    selectedPlacesAlert.style.display = 'none';
                } else {
                    const selectedNames = Array.from(placeCheckboxes)
                        .filter(checkbox => checkbox.checked)
                        .map(checkbox => checkbox.value);

                    selectedPlacesText.textContent = selectedNames.join('، ');
                    selectedPlacesAlert.style.display = 'block';
                }
            }

            function wireDeleteButtons() {
                if (typeof initDeleteHandler === 'function') {
                    initDeleteHandler('strawberryPayment');
                }

                const deleteButtons = wrapper.querySelectorAll('.delete-btn');
                const deleteForm = document.getElementById('deleteStrawberryPaymentForm');

                if (!deleteForm) return;

                deleteButtons.forEach(button => {
                    if (button.dataset.deleteActionBound === 'true') return;

                    button.dataset.deleteActionBound = 'true';

                    button.addEventListener('click', function() {
                        deleteForm.action = this.dataset.url;
                    });
                });
            }

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

                params.append('places_filter', '1');

                getSelectedPlaces().forEach(place => {
                    params.append('places[]', place);
                });

                try {
                    const response = await fetch(
                        `{{ route('strawberry-customer-payments.byDate', ['date' => $carbonDate]) }}?${params.toString()}`, {
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
                        console.error('Error loading Strawberry payments:', error);
                    }
                } finally {
                    if (activeRequest === controller) {
                        activeRequest = null;
                    }
                }
            }

            nameInput.addEventListener('input', fetchFilteredTable);

            placeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updatePlaceSelection();
                    fetchFilteredTable();
                });
            });

            updatePlaceSelection();
            wireDeleteButtons();
        });
    </script>
@endsection
