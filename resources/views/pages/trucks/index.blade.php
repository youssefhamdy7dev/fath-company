@extends('layouts.app')

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

        <h1 class="mb-4">قائمة العربات</h1>

        {{-- ================= UNFINISHED TRUCKS ================= --}}
        <h4 class="text-danger mb-2">العربات الغير مكتملة</h4>

        <div class="table-responsive w-100">
            <table class="table table-secondary table-bordered table-hover align-middle">
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
                        <tr class="{{ $truck->date->isToday() ? 'table-info' : '' }}">
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
            <table class="table table-secondary table-bordered table-hover align-middle">
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
                        <tr class="{{ $truck->date->isToday() ? 'table-info' : '' }}">
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
@endsection
