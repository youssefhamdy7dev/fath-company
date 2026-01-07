@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card card-background mb-4 p-4">
            <div class="text-end">
                <a href="{{ route('bills.edit', $bill) }}" class="btn btn-warning">
                    تعديل <i class="bi bi-pencil-square"></i>
                </a>
                <a href="{{ route('bills.index') }}" class="btn btn-dark">
                    الرجوع للقائمة السابقة <i class="bi bi-arrow-up-left ps-1"></i>
                </a>
            </div>

            <h2 class="text-center mb-4">بيانات الفاتورة</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">تاريخ الفاتورة</h6>
                    <div class="fs-5 fw-bold">
                        {{ transform_numbers($bill->truck->date->format('d-m-Y')) }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">تاريخ الصرف</h6>
                    <div class="fs-5 fw-bold text-danger">
                        {{ transform_numbers($bill->billing_date->format('d-m-Y')) }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">العميل</h6>
                    <div class="fs-5 fw-bold">
                        {{ $bill->truck->client_names ?? 'مشتروات' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">النولون</h6>
                    <div class="fs-5 fw-bold">
                        {{ transform_numeric_value($bill->truck->freight) }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">العمولة (%)</h6>
                    <div class="fs-5 fw-bold">
                        {{ transform_numeric_value($bill->percentage) }}%
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">الصنف / الأصناف</h6>
                    <div class="fs-5 fw-bold">
                        {{ $bill->truck->fruit_names }}
                    </div>
                </div>

                <div class="col-md-4">
                    <h6 class="text-muted">عدد البرانيك</h6>
                    <div class="fs-5 fw-bold">
                        {{ transform_numeric_value($bill->truck->total_boxes) }}
                    </div>
                </div>

                <div class="col-md-4">
                    <h6 class="text-muted">المبلغ</h6>
                    <div class="text-success fs-5 fw-bold">
                        {{ transform_numeric_value(round_to_nearest_tenth($bill->bill_price)) }}
                    </div>
                </div>

                <div class="col-md-4">
                    <h6 class="text-muted">الملاحظات</h6>
                    <div class="fs-5 fw-bold text-secondary">
                        {{ $bill->notes ? $bill->notes : 'لا يوجد ملاحظات' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm p-4">
            <div class="table-responsive">
                <table class="table table-secondary table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>عدد</th>
                            <th>وزن</th>
                            <th>فئة</th>
                            <th>مبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bill->items->sortByDesc('price') as $item)
                            <tr class="fw-bold">
                                <td>{{ transform_numeric_value($item->total_boxes) }}</td>
                                <td>{{ transform_numeric_value($item->total_weight) }}</td>
                                <td>{{ transform_numbers($item->price) }}</td>
                                <td>{{ transform_numeric_value($item->total_amount) }}</td>
                            </tr>
                        @endforeach
                        {{-- TOTAL --}}
                        <tr class="table-light fw-bold fs-5">
                            <td colspan="3" class="text-danger">الإجمالي</td>
                            <td class="text-danger">{{ transform_numeric_value($bill->grand_total) }}</td>
                        </tr>
                        {{-- PERCENTAGE --}}
                        <tr class="table-light fw-bold fs-5">
                            <td colspan="3">العمولة</td>
                            <td>{{ transform_numeric_value(intval(floor(($bill->percentage / 100) * $bill->grand_total))) }}
                            </td>
                        </tr>
                        {{-- FREIGHT --}}
                        <tr class="table-light fw-bold fs-5">
                            <td colspan="3">النولون</td>
                            <td>{{ transform_numeric_value($bill->truck->freight) }}</td>
                        </tr>
                        {{-- EXPENSES --}}
                        <tr class="table-light fw-bold fs-5">
                            <td colspan="3">الخوارج / المصاريف</td>
                            <td>{{ transform_numeric_value($bill->expenses ?? 0) }}</td>
                        </tr>
                        {{-- FINAL TOTAL (ACCESSOR) --}}
                        <tr class="table-light fw-bold fs-4">
                            <td colspan="3" class="text-success">صافى الفاتورة</td>
                            <td class="text-success">
                                {{ transform_numeric_value(round_to_nearest_tenth($bill->bill_price)) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
