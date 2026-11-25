<div class="mb-5 border rounded p-3 bill-client-group" data-client-id="{{ $group['client']->id }}">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="mb-0 text-primary">
            {{ $group['client']->name }}
        </h4>

        <div class="fw-bold text-danger bill-client-total d-none">
            إجمالي الفواتير:
            <span class="bill-total-value">0</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-secondary table-bordered table-hover align-middle text-center">
            <thead>
                <tr class="table-dark">
                    <th>#</th>
                    <th>تاريخ الفاتورة</th>
                    <th>تاريخ الصرف</th>
                    <th class="text-danger">المبلغ</th>
                    <th>الصنف / الأصناف</th>
                    <th>ملاحظات</th>
                    <th>إجراءات</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($group['bills'] as $bill)
                    <tr class="bill-row" data-date="{{ $bill->truck?->date?->format('Y-m-d') }}"
                        data-fruit-ids="{{ $bill->truck?->truckFruits->pluck('fruit_id')->unique()->implode(',') }}"
                        data-amount="{{ $bill->index_bill_price }}">
                        <td class="bill-row-number">
                            {{ transform_numbers($loop->iteration) }}
                        </td>

                        <td class="text-primary">
                            {{ transform_numbers($bill->truck->date->format('d-m-Y')) }}
                        </td>

                        <td class="text-danger">
                            {{ transform_numbers($bill->billing_date->format('d-m-Y')) }}
                        </td>

                        <td class="fw-bolder fs-5 text-danger">
                            {{ transform_numeric_value(round_to_nearest_tenth($bill->index_bill_price)) }}
                        </td>

                        <td>
                            {{ $bill->truck->fruit_names }}
                        </td>

                        <td>
                            {{ $bill->notes ?? 'لا يوجد ملاحظات' }}
                        </td>

                        <td>
                            <a href="{{ route('bills.show', $bill) }}" class="btn btn-info btn-sm">
                                عرض <i class="bi bi-eye-fill"></i>
                            </a>

                            <a href="{{ route('bills.edit', $bill) }}" class="btn btn-warning btn-sm">
                                تعديل <i class="bi bi-pencil-square ps-1"></i>
                            </a>

                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $bill->id }}"
                                data-name="{{ transform_numbers($bill->billing_date->format('d-m-Y')) }}"
                                data-bs-toggle="modal" data-bs-target="#deleteBillModal">
                                حذف <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bill-pagination d-flex justify-content-center mt-3"></div>
</div>
