    @extends('layouts.app')
    <title>شركة الفتح | العربات</title>

    @section('content')
        <div class="card shadow-sm p-4 align-items-center align-content-center text-center">

            {{-- Alerts --}}
            <x-alerts.session-status />

            {{-- Top buttons --}}
            <div class="d-flex justify-content-between w-100 mb-3">
                <a href="{{ route('customers.reports') }}" class="btn btn-dark">
                    كشف مشتروات الزبائن <i class="ps-1 bi bi-cart4"></i>
                </a>
                <div>
                    <a href="{{ route('bills.index') }}" class="btn btn-outline-danger">
                        إدارة الفواتير <i class="ps-1 bi bi-receipt-cutoff"></i>
                    </a>
                    <a href="{{ route('trucks.create') }}" class="btn btn-success">
                        إضافة عربة جديدة <i class="ps-1 bi bi-plus-square"></i>
                    </a>
                </div>
            </div>
            {{-- ================= TRUCK FILTERS ================= --}}
            <div class="row g-3 w-100 mb-4 text-start">

                <div class="col-md-4">
                    <label for="filter-date" class="form-label">
                        البحث بالتاريخ
                    </label>

                    <input type="date" id="filter-date" class="globdatepicker form-control">
                </div>

                <div class="col-md-4">
                    <label for="filter-driver" class="form-label">
                        البحث بالسائق
                    </label>

                    <select id="filter-driver" class="form-select">
                        <option value="">السائق</option>

                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-fruit" class="form-label">
                        البحث بالصنف
                    </label>

                    <select id="filter-fruit" class="form-select">
                        <option value="">الصنف</option>

                        @foreach ($fruits as $fruit)
                            <option value="{{ $fruit->id }}">
                                {{ $fruit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 text-end">
                    <button type="button" id="reset-truck-filters" class="btn btn-outline-danger">
                        إلغاء البحث <i class="ps-1 bi bi-x-circle"></i>
                    </button>
                </div>

            </div>
            <h1 class="mb-4">قائمة العربات</h1>

            {{-- ================= UNFINISHED TRUCKS ================= --}}
            <h4 class="text-danger mb-2">العربات الغير مكتملة</h4>

            <div class="table-responsive w-100">
                <table class="table table-secondary table-bordered table-hover text-center align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>#</th>
                            <th>الصنف / الأصناف</th>
                            <th>العدد</th>
                            <th>التاريخ</th>
                            <th>السائق</th>
                            <th>العميل</th>
                            <th>الحالة</th>
                            <th>الصرف</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unfinishedTrucks as $truck)
                            <tr data-truck-row data-date="{{ $truck->date->format('Y-m-d') }}"
                                data-driver-id="{{ $truck->driver_id ?? '' }}"
                                data-fruit-ids="{{ $truck->truckFruits->pluck('fruit_id')->unique()->implode(',') }}"
                                class="{{ $truck->date->isToday() ? 'table-info' : '' }}">
                                <th scope="row">
                                    {{ transform_numbers(($unfinishedTrucks->currentPage() - 1) * $unfinishedTrucks->perPage() + $loop->iteration) }}
                                </th>
                                <td>{{ $truck->fruit_names }}</td>
                                <td>{{ transform_numeric_value($truck->total_boxes) }}</td>
                                <td>{{ transform_numbers($truck->date->format('d-m-Y')) }}</td>
                                <td>{{ $truck->driver?->name ?? '—' }}</td>
                                <td>{{ $truck->client_names ?? 'مشتروات' }}</td>
                                <td>
                                    <x-status-chip type="completed" :value="$truck->numberOfBoughtBoxes == $truck->total_boxes" />
                                </td>
                                <td>
                                    <x-status-chip type="billed" :value="(bool) $truck->bill" />
                                </td>
                                <td class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('trucks.show', $truck->id) }}" class="btn btn-info btn-sm">
                                        عرض <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('trucks.edit', $truck->id) }}" class="btn btn-warning btn-sm">
                                        تعديل <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted text-center">
                                    لا توجد عربات غير مكتملة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $unfinishedTrucks->links() }}
            </div>

            {{-- ================= FINISHED TRUCKS ================= --}}
            <h4 class="text-success mt-5 mb-2">العربات المكتملة</h4>

            <div class="table-responsive w-100">
                <table class="table table-secondary table-bordered table-hover text-center align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>#</th>
                            <th>الصنف / الأصناف</th>
                            <th>العدد</th>
                            <th>التاريخ</th>
                            <th>السائق</th>
                            <th>العميل</th>
                            <th>الحالة</th>
                            <th>الصرف</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($finishedTrucks as $truck)
                            <tr data-truck-row data-date="{{ $truck->date->format('Y-m-d') }}"
                                data-driver-id="{{ $truck->driver_id ?? '' }}"
                                data-fruit-ids="{{ $truck->truckFruits->pluck('fruit_id')->unique()->implode(',') }}"
                                class="{{ $truck->date->isToday() ? 'table-info' : '' }}">
                                <th scope="row">
                                    {{ transform_numbers(($finishedTrucks->currentPage() - 1) * $finishedTrucks->perPage() + $loop->iteration) }}
                                </th>
                                <td>{{ $truck->fruit_names }}</td>
                                <td>{{ transform_numeric_value($truck->total_boxes) }}</td>
                                <td>{{ transform_numbers($truck->date->format('d-m-Y')) }}</td>
                                <td>{{ $truck->driver?->name ?? '—' }}</td>
                                <td>{{ $truck->client_names ?? 'مشتروات' }}</td>
                                <td>
                                    <x-status-chip type="completed" :value="$truck->numberOfBoughtBoxes == $truck->total_boxes" />
                                </td>
                                <td>
                                    <x-status-chip type="billed" :value="(bool) $truck->bill" />
                                </td>
                                <td class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('trucks.show', $truck->id) }}" class="btn btn-info btn-sm">
                                        عرض <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('trucks.edit', $truck->id) }}" class="btn btn-warning btn-sm">
                                        تعديل <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted text-center">
                                    لا توجد عربات مكتملة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $finishedTrucks->links() }}
            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dateFilter = document.getElementById('filter-date');
                const driverFilter = document.getElementById('filter-driver');
                const fruitFilter = document.getElementById('filter-fruit');
                const resetButton = document.getElementById('reset-truck-filters');

                // Includes rows from both finished and unfinished tables.
                const truckRows = document.querySelectorAll('[data-truck-row]');

                function filterTrucks() {
                    const selectedDate = dateFilter.value;
                    const selectedDriver = driverFilter.value;
                    const selectedFruit = fruitFilter.value;

                    truckRows.forEach(function(row) {
                        const truckDate = row.dataset.date;
                        const truckDriver = row.dataset.driverId;

                        const truckFruits = row.dataset.fruitIds
                            .split(',')
                            .filter(Boolean);

                        const matchesDate = !selectedDate || truckDate === selectedDate;

                        const matchesDriver = !selectedDriver || truckDriver === selectedDriver;

                        const matchesFruit = !selectedFruit || truckFruits.includes(selectedFruit);

                        row.style.display =
                            matchesDate && matchesDriver && matchesFruit ?
                            '' :
                            'none';
                    });
                }

                dateFilter.addEventListener('change', filterTrucks);
                driverFilter.addEventListener('change', filterTrucks);
                fruitFilter.addEventListener('change', filterTrucks);

                resetButton.addEventListener('click', function() {
                    dateFilter.value = '';
                    driverFilter.value = '';
                    fruitFilter.value = '';

                    filterTrucks();
                });
            });
        </script>
    @endsection
