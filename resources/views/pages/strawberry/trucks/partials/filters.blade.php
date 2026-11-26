<form method="GET" action="{{ route('strawberry-trucks.index') }}" id="strawberryTruckFiltersForm" class="w-100 mb-4"
    dir="rtl">

    <div class="row g-3 align-items-end text-start">

        <div class="col-md-4">
            <label for="filter-date" class="form-label">البحث بالتاريخ</label>

            <input type="date" name="date" id="filter-date" class="globdatepicker form-control"
                value="{{ $filters['date'] ?? '' }}" autoComplete="off">
        </div>

        <div class="col-md-4">
            <label class="form-label">البحث بالسائق</label>

            <div class="dropdown places-dropdown-wrapper w-100">
                <button type="button"
                    class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                    data-bs-toggle="dropdown" id="strawberryDriversDropdownButton">

                    <span id="strawberryDriversDropdownLabel">اختر السائقين</span>
                </button>

                <div class="dropdown-menu places-dropdown-menu w-100 p-2">
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

        <div class="col-md-4">
            <label class="form-label">البحث بالصنف</label>

            <div class="dropdown places-dropdown-wrapper w-100">
                <button type="button"
                    class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                    data-bs-toggle="dropdown" id="strawberryTypesDropdownButton">

                    <span id="strawberryTypesDropdownLabel">اختر الأصناف</span>
                </button>

                <div class="dropdown-menu places-dropdown-menu w-100 p-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button type="button" class="btn btn-sm btn-link p-0" data-select-all="types">
                            تحديد الكل
                        </button>

                        <button type="button" class="btn btn-sm btn-link text-danger p-0" data-clear-all="types">
                            إلغاء التحديد
                        </button>
                    </div>

                    <div class="places-dropdown-checklist">
                        @foreach ($fruitTypes as $fruitType)
                            <label class="dropdown-item d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input mt-0" name="fruit_types[]"
                                    value="{{ $fruitType }}" data-filter-type="types" @checked(in_array($fruitType, $filters['fruit_types'] ?? []))>

                                <span>{{ $fruitType }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">
                تطبيق البحث <i class="ps-1 bi bi-search"></i>
            </button>

            <a href="{{ route('strawberry-trucks.index') }}" class="btn btn-outline-danger">
                إلغاء البحث <i class="ps-1 bi bi-x-circle"></i>
            </a>
        </div>

    </div>
</form>

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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('strawberryTruckFiltersForm');

        if (!form) return;

        const dropdowns = {
            drivers: {
                checkboxes: form.querySelectorAll('[data-filter-type="drivers"]'),
                label: document.getElementById('strawberryDriversDropdownLabel'),
                defaultLabel: 'اختر السائقين',
            },
            types: {
                checkboxes: form.querySelectorAll('[data-filter-type="types"]'),
                label: document.getElementById('strawberryTypesDropdownLabel'),
                defaultLabel: 'اختر الأصناف',
            },
        };

        function updateLabel(type) {
            const dropdown = dropdowns[type];

            const count = Array.from(dropdown.checkboxes)
                .filter(checkbox => checkbox.checked).length;

            dropdown.label.textContent = count ?
                `${count} محدد` :
                dropdown.defaultLabel;
        }

        Object.keys(dropdowns).forEach(type => {
            updateLabel(type);

            dropdowns[type].checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => updateLabel(type));
            });
        });

        form.querySelectorAll('[data-select-all], [data-clear-all]')
            .forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.dataset.selectAll || this.dataset.clearAll;
                    const shouldSelect = this.hasAttribute('data-select-all');

                    dropdowns[type].checkboxes.forEach(checkbox => {
                        checkbox.checked = shouldSelect;
                    });

                    updateLabel(type);
                });
            });
    });
</script>
