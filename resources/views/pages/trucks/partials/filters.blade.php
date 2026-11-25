{{-- Truck filters --}}
<form method="GET" action="{{ route('trucks.index') }}" id="truckFiltersForm" class="w-100 mb-4" dir="rtl">
    <div class="row g-3 align-items-end text-start">

        {{-- Date filter --}}
        <div class="col-md-4">
            <label for="filter-date" class="form-label">
                البحث بالتاريخ
            </label>

            <input type="date" name="date" id="filter-date" class="globdatepicker form-control"
                value="{{ $filters['date'] ?? '' }}">
        </div>

        {{-- Drivers filter --}}
        <div class="col-md-4">
            <label class="form-label">البحث بالسائق</label>

            <div class="dropdown places-dropdown-wrapper w-100">
                <button type="button"
                    class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                    data-bs-toggle="dropdown" aria-expanded="false" id="truckDriversDropdownButton">
                    <span id="truckDriversDropdownLabel">اختر السائقين</span>
                </button>

                <div class="dropdown-menu places-dropdown-menu w-100 p-2" aria-labelledby="truckDriversDropdownButton">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button type="button" class="btn btn-sm btn-link p-0" data-select-all="drivers">
                            تحديد الكل
                        </button>

                        <button type="button" class="btn btn-sm btn-link text-danger p-0" data-clear-all="drivers">
                            إلغاء التحديد
                        </button>
                    </div>

                    <div class="places-dropdown-checklist">
                        @foreach ($drivers as $driver)
                            <label class="dropdown-item d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input mt-0" name="driver_ids[]"
                                    value="{{ $driver->id }}" data-filter-type="drivers" @checked(in_array($driver->id, $filters['driver_ids'] ?? []))>
                                <span>{{ $driver->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Fruits filter --}}
        <div class="col-md-4">
            <label class="form-label">البحث بالصنف</label>

            <div class="dropdown places-dropdown-wrapper w-100">
                <button type="button"
                    class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                    data-bs-toggle="dropdown" aria-expanded="false" id="truckFruitsDropdownButton">
                    <span id="truckFruitsDropdownLabel">اختر الأصناف</span>
                </button>

                <div class="dropdown-menu places-dropdown-menu w-100 p-2" aria-labelledby="truckFruitsDropdownButton">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button type="button" class="btn btn-sm btn-link p-0" data-select-all="fruits">
                            تحديد الكل
                        </button>

                        <button type="button" class="btn btn-sm btn-link text-danger p-0" data-clear-all="fruits">
                            إلغاء التحديد
                        </button>
                    </div>

                    <div class="places-dropdown-checklist">
                        @foreach ($fruits as $fruit)
                            <label class="dropdown-item d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input mt-0" name="fruit_ids[]"
                                    value="{{ $fruit->id }}" data-filter-type="fruits" @checked(in_array($fruit->id, $filters['fruit_ids'] ?? []))>
                                <span>{{ $fruit->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter actions --}}
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">
                تطبيق البحث <i class="ps-1 bi bi-search"></i>
            </button>

            <a href="{{ route('trucks.index') }}" class="btn btn-outline-danger">
                إلغاء البحث <i class="ps-1 bi bi-x-circle"></i>
            </a>
        </div>

    </div>
</form>

{{-- Dropdown styles --}}
<style>
    .places-dropdown-menu {
        max-height: 350px;
        overflow-y: auto;
        z-index: 1055;
    }

    .places-dropdown-checklist .dropdown-item {
        cursor: pointer;
        white-space: normal;
        padding: 8px 10px;
    }

    .places-dropdown-checklist .dropdown-item:hover {
        background-color: var(--bs-tertiary-bg);
    }

    .places-dropdown-checklist .form-check-input {
        flex-shrink: 0;
    }
</style>

{{-- Dropdown behavior --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('truckFiltersForm');

        if (!form) {
            return;
        }

        const dropdowns = {
            drivers: {
                checkboxes: form.querySelectorAll('[data-filter-type="drivers"]'),
                label: document.getElementById('truckDriversDropdownLabel'),
                defaultLabel: 'اختر السائقين',
            },
            fruits: {
                checkboxes: form.querySelectorAll('[data-filter-type="fruits"]'),
                label: document.getElementById('truckFruitsDropdownLabel'),
                defaultLabel: 'اختر الأصناف',
            },
        };

        // Update dropdown label
        function updateDropdownLabel(type) {
            const dropdown = dropdowns[type];

            const selectedCount = Array.from(dropdown.checkboxes)
                .filter(checkbox => checkbox.checked).length;

            dropdown.label.textContent = selectedCount ?
                `${selectedCount} محدد` :
                dropdown.defaultLabel;
        }

        // Listen for checkbox changes
        Object.keys(dropdowns).forEach(type => {
            updateDropdownLabel(type);

            dropdowns[type].checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => updateDropdownLabel(type));
            });
        });

        // Select all / Clear all
        form.querySelectorAll('[data-select-all], [data-clear-all]').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.selectAll || this.dataset.clearAll;
                const shouldSelect = this.hasAttribute('data-select-all');

                dropdowns[type].checkboxes.forEach(checkbox => {
                    checkbox.checked = shouldSelect;
                });

                updateDropdownLabel(type);
            });
        });
    });
</script>
