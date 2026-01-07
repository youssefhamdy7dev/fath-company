@if (count($groupedPurchases) < 1)
    <div class="card p-4 shadow-sm mb-4">
        <div class="fs-4 fw-bolder text-muted text-center">لا توجد مشتروات.</div>
    </div>
@else
    @foreach ($dates as $row)
        @php
            $dateKey = \Carbon\Carbon::parse($row->ledger_date)->format('Y-m-d');

            $rows = $groupedPurchases[$dateKey] ?? collect();
            $balance = $balances[$dateKey];
        @endphp
        <h4 class="mt-4 text-primary fw-bold text-center">
            {{ transform_numbers(\Carbon\Carbon::parse($dateKey)->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
            @if (\Carbon\Carbon::parse($dateKey)->isToday())
                <span class="text-danger">- اليوم</span>
            @endif
        </h4>
        <div class="card p-4 shadow-sm mb-4">
            <div class="table-responsive mb-4">
                <table
                    class="table table-bordered {{ \Carbon\Carbon::parse($dateKey)->isToday() ? 'table-info' : 'table-secondary' }} table-hover text-center align-middle">
                    @if ($rows->isNotEmpty())
                        <thead class="table-dark">
                            <tr>
                                <th>الصنف</th>
                                <th>العدد</th>
                                <th>الوزن</th>
                                <th>الفئة</th>
                                <th class="text-primary">الإجمالى</th>
                                <th>النوع</th>
                                <th>حجم البرنيكة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    $style = $row->computed_class_name == 'فاخر' ? '' : 'text-danger';
                                @endphp
                                <tr>
                                    <td class={{ $style }}>{{ $row->fruit_name }}</td>

                                    <td class={{ $style }}>{{ transform_numeric_value($row->number_of_boxes) }}
                                    </td>

                                    <td class="fw-bolder fs-5 {{ $style }}">
                                        {{ $row->total_weight ? transform_numeric_value($row->total_weight) : '-' }}
                                    </td>

                                    <td class="fw-bolder fs-5 {{ $style }}">
                                        {{ transform_numbers($row->computed_unit_price ?: $row->computed_box_price) }}
                                    </td>

                                    <td class="fw-bolder text-primary fs-5">
                                        {{ transform_numeric_value($row->computed_total) }}
                                    </td>

                                    <td class={{ $style }}>{{ $row->computed_class_name }}</td>

                                    <td class={{ $style }}>{{ $row->box_type_name }}</td>

                                    <td class="d-flex justify-content-center gap-2">
                                        {{-- update --}}
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editCustomerPurchaseModal{{ $row->id }}">
                                            تعديل <i class="bi bi-pencil-square"></i>
                                        </button>

                                        {{-- go to truck --}}
                                        <a href="{{ route('trucks.show', $row->truckFruit->truck_id) }}"
                                            class="btn btn-info btn-sm">
                                            الذهاب للعربة <i class="bi bi-truck-front-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                @push('modals')
                                    @include('pages.customers.partials.edit-customer-purchase-modal')
                                @endpush
                            @endforeach
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4" class="text-secondary">إجمالى المشتروات</td>
                                <td>{{ transform_numeric_value($balance['totalPurchases']) }}</td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4" class="text-danger">حساب الزبون (ما قبله)</td>
                                <td class="text-danger">{{ transform_numeric_value($balance['remaining']) }}
                                </td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4" class="text-success">تحصيل
                                    {{ transform_numbers($balance['date']->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
                                </td>
                                <td class="text-success">
                                    {{ transform_numeric_value($balance['payment']) }}
                                </td>
                                <td colspan="3"></td>
                            </tr>
                            @if ($balance['discount'] > 0)
                                <tr class="fw-bolder fs-5 text-center">
                                    <td colspan="4">خصم</td>
                                    <td class="text-success">
                                        {{ transform_numeric_value($balance['discount']) }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            @endif
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4" class="text-primary-emphasis">باقى بعد التحصيل</td>
                                <td class="text-primary-emphasis">
                                    {{ transform_numeric_value($balance['remaining'] - ($balance['payment'] + $balance['discount'] ?? 0)) }}
                                <td colspan="3"></td>
                                </td>
                            </tr>
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4">اجمالى الحساب النهائى</td>
                                <td>{{ transform_numeric_value($balance['final']) }}</td>
                                <td colspan="3" class="text-muted fst-italic" style="font-size: smaller">إجمالى ما
                                    قبله +
                                    إجمالى المشتروات
                                </td>
                            </tr>
                        </tbody>
                    @else
                        <thead class="table-success">
                            <tr>
                                <th colspan="8" class="text-success-emphasis">تحصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-muted fs-5 fw-bolder">لا يوجد مشتروات</td>
                            </tr>
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4" class="text-danger">حساب الزبون (ما قبله)</td>
                                <td class="text-danger">{{ transform_numeric_value($balance['remaining']) }}
                                </td>
                            </tr>
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4" class="text-success">تحصيل
                                    {{ transform_numbers($balance['date']->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
                                </td>
                                <td class="text-success">
                                    {{ transform_numeric_value($balance['payment']) }}
                                </td>
                            </tr>
                            @if ($balance['discount'] > 0)
                                <tr class="fw-bolder fs-5 text-center">
                                    <td colspan="4">خصم</td>
                                    <td class="text-success">
                                        {{ transform_numeric_value($balance['discount']) }}
                                    </td>
                                </tr>
                            @endif
                            <tr class="fw-bolder fs-5 text-center">
                                <td colspan="4" class="text-primary-emphasis">باقى بعد التحصيل</td>
                                <td class="text-primary-emphasis">
                                    {{ transform_numeric_value($balance['remaining'] - ($balance['payment'] + $balance['discount'] ?? 0)) }}
                                </td>
                            </tr>
                        </tbody>
                    @endif

                </table>
            </div>
        </div>
    @endforeach
@endif
<div class="mt-4">
    {{ $dates->links() }}
</div>
