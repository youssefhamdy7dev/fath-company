<h3 class="mt-4 text-primary fw-bold text-center">
    <a href="{{ route('customers.show', $data['customer']->id) }}"> {{ $data['customer']->name }}</a>
    — <span class="text-muted">{{ $data['customer']->location }}</span>
</h3>

<div class="table-responsive mb-4">

    <table class="table table-bordered table-secondary table-hover text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>الصنف</th>
                <th>العدد</th>
                <th>حجم البرنيكة</th>
                <th>الوزن</th>
                <th>الفئة</th>
                <th class="text-danger">الإجمالى</th>
            </tr>
        </thead>

        <tbody>
            @forelse($data['purchases'] as $row)
                <tr>
                    <td>{{ $row->fruit_name }}</td>
                    <td>{{ transform_numeric_value($row->number_of_boxes) }}</td>
                    <td>{{ $row->box_type_name }}</td>

                    <td class="fw-bolder fs-5">
                        {{ $row->total_weight ? transform_numeric_value($row->total_weight) : '-' }}
                    </td>

                    <td class="fw-bolder fs-5">
                        {{ transform_numbers($row->computed_unit_price ?: $row->computed_box_price) }}
                    </td>

                    <td class="fw-bolder fs-5">
                        {{ transform_numeric_value($row->computed_total) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">لا توجد مشتروات لهذا الزبون في هذا التاريخ.</td>
                </tr>
            @endforelse

            {{-- TOTALS --}}
            {{-- <tr class="fw-bolder fs-5 text-center">
                <td colspan="5">إجمالى المشتروات </td>
                <td>{{ transform_numeric_value($data['totalPurchases']) }}</td>
            </tr> --}}

            <tr class="fw-bolder fs-5 text-center">
                <td colspan="5" class="text-danger">ما قبله</td>
                <td class="text-danger">
                    {{ transform_numeric_value($data['remaining'] - ($data['payment'] + $data['discount'])) }}</td>
            </tr>

            {{-- <tr class="fw-bolder fs-5 text-center">
                <td colspan="5" class="text-success">
                    تحصيل
                    {{ transform_numbers(\Carbon\Carbon::parse($date)->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
                </td>
                <td class="text-success">{{ transform_numeric_value($data['payment']) }}</td>
            </tr> --}}

            {{-- @if ($data['discount'] > 0)
                <tr class="fw-bolder fs-5 text-center">
                    <td colspan="5">خصم</td>
                    <td class="text-success">{{ transform_numeric_value($data['discount']) }}</td>
                </tr>
            @endif --}}

            <tr class="fw-bolder fs-5 text-center">
                <td colspan="5">باقى الحساب</td>
                <td>{{ transform_numeric_value($data['final']) }}</td>
            </tr>
        </tbody>
    </table>

</div>
