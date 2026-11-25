@props([
    'places' => [],
    'selectedPlaceKeys' => [],
    'checkboxName' => null,
    'checkboxClass' => '',
    'buttonClass' => 'btn btn-outline-secondary',
    'menuMaxHeight' => null,
])

<div class="dropdown places-dropdown-wrapper" id="placesDropdownWrapper">

    <button class="{{ $buttonClass }} dropdown-toggle w-100 text-end d-flex justify-content-between align-items-center"
        type="button" id="placesDropdownButton" data-bs-toggle="dropdown" data-bs-auto-close="outside"
        aria-expanded="false">
        <span id="placesDropdownLabel">
            كل المناطق
        </span>
    </button>

    <div class="dropdown-menu shadow p-3 places-dropdown-menu" id="placesDropdownMenu"
        aria-labelledby="placesDropdownButton"
        @if ($menuMaxHeight) style="max-height: {{ $menuMaxHeight }}; overflow-y: auto;" @endif>

        {{-- Dropdown Header --}}
        <div class="d-flex justify-content-between align-items-center mb-2">

            <strong>اختر المناطق</strong>

            <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0"
                id="clearPlaceSelection">
                إلغاء التحديد
            </button>

        </div>

        <hr class="my-2">

        {{-- Places Checklist --}}
        @foreach ($places as $placeName => $placeLocations)
            <div class="form-check mb-2">

                <input class="form-check-input js-place-checkbox {{ $checkboxClass }}" type="checkbox"
                    @if ($checkboxName) name="{{ $checkboxName }}" @endif value="{{ $placeName }}"
                    id="placeOption{{ $loop->index }}" data-locations='@json($placeLocations)'
                    @checked(in_array($placeName, $selectedPlaceKeys, true))>

                <label class="form-check-label" for="placeOption{{ $loop->index }}">
                    {{ $placeName }}
                </label>

            </div>
        @endforeach

        <hr class="my-2">

        {{-- Dropdown Footer --}}
        <div class="d-flex justify-content-between align-items-center">

            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPlaces">
                تحديد الكل
            </button>

            <small class="text-muted" id="placesSelectionCount">
                كل المناطق
            </small>

        </div>

    </div>

</div>

@once

    <style>
        /* Shared Places Dropdown */
        .places-dropdown-wrapper {
            position: relative;
            width: 300px;
        }

        .places-dropdown-menu {
            width: 100%;
            min-width: 300px;
            overflow-x: hidden;
            overflow-y: auto;
            text-align: right;
            direction: rtl;
        }

        .places-dropdown-menu .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-right: 0;
        }

        .places-dropdown-menu .form-check-input {
            float: none;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
        }

        .places-dropdown-menu .form-check-label {
            cursor: pointer;
            flex: 1;
            margin: 0;
        }

        .places-dropdown-menu .form-check:hover {
            background-color: rgba(0, 0, 0, 0.04);
        }

        @media (max-width: 576px) {
            .places-dropdown-wrapper {
                width: 220px;
            }

            .places-dropdown-menu {
                min-width: 220px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const wrapper = document.getElementById('placesDropdownWrapper');

            if (!wrapper) return;

            const checkboxes = wrapper.querySelectorAll('.js-place-checkbox');

            const dropdownLabel = document.getElementById('placesDropdownLabel');
            const selectionCount = document.getElementById('placesSelectionCount');

            const clearButton = document.getElementById('clearPlaceSelection');
            const selectAllButton = document.getElementById('selectAllPlaces');

            const selectedPlacesAlert = document.querySelector('.js-selected-places-alert');
            const selectedPlacesNames = document.querySelector('.js-selected-places-names');

            const allPlaceKeys = Array.from(checkboxes).map(
                checkbox => checkbox.value
            );

            function getSelectedPlaceKeys() {
                return Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);
            }

            function getSelectedLocations() {
                const locations = new Set();

                checkboxes.forEach(checkbox => {

                    if (!checkbox.checked) return;

                    const placeLocations = JSON.parse(
                        checkbox.dataset.locations || '[]'
                    );

                    placeLocations.forEach(location => {
                        locations.add(location);
                    });

                });

                return Array.from(locations);
            }

            function updateSelection(dispatchEvent = true) {

                const selectedPlaceKeys = getSelectedPlaceKeys();

                const checkedCount = selectedPlaceKeys.length;

                const showAll =
                    checkedCount === 0 ||
                    checkedCount === allPlaceKeys.length;

                if (showAll) {

                    dropdownLabel.textContent = 'كل المناطق';

                    selectionCount.textContent = 'كل المناطق';

                    if (selectedPlacesAlert) {
                        selectedPlacesAlert.style.display = 'none';
                    }

                } else {

                    dropdownLabel.textContent =
                        `تم تحديد ${checkedCount} مناطق`;

                    selectionCount.textContent =
                        `${checkedCount} من ${allPlaceKeys.length}`;

                    if (selectedPlacesAlert) {
                        selectedPlacesAlert.style.display = 'block';
                    }

                }

                if (selectedPlacesNames) {

                    selectedPlacesNames.textContent = showAll ?
                        'يتم عرض جميع المناطق' :
                        selectedPlaceKeys.join('، ');

                }

                if (dispatchEvent) {

                    wrapper.dispatchEvent(new CustomEvent('places:changed', {
                        bubbles: true,
                        detail: {
                            selectedPlaceKeys,
                            selectedLocations: getSelectedLocations(),
                            showAll
                        }
                    }));

                }

            }

            // Individual checkbox changes
            checkboxes.forEach(checkbox => {

                checkbox.addEventListener('change', function() {
                    updateSelection();
                });

            });

            // Clear all selections
            clearButton.addEventListener('click', function(event) {
                event.preventDefault();

                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });
            });

            // Select all places
            selectAllButton.addEventListener('click', function(event) {
                event.preventDefault();

                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });
            });

            // Initial dropdown state
            updateSelection(false);

        });
    </script>

@endonce
