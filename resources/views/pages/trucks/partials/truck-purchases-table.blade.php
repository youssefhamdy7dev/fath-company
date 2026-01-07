<div class="card shadow-sm p-4" dir="rtl">
    <h4 class="text-center mb-3">
        قائمة التوزيع
    </h4>

    @if ($truck->truckFruits->flatMap->customerPurchases->isEmpty())
        <p class="text-center text-muted mb-0">لم يتم إضافة مشتروات بعد.</p>
    @else
        <div class="table-responsive">
            <table class="table table-danger table-bordered text-center table-hover align-middle">
                <thead>
                    <tr class="table-dark">
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الزبون</th>
                        <th>عدد</th>
                        <th>نوع البرنيك</th>
                        <th>الوزن</th>
                        <th>الفئة</th>
                        <th class="text-danger">إجمالى</th>
                        <th>النوع</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($truck->truckFruits as $tf)
                        @foreach ($tf->customerPurchases as $cp)
                            @php
                                $dangerClass = $cp->computed_class_name == 'نمرة 2' ? 'text-danger' : '';
                            @endphp
                            <tr
                                class="{{ $cp->date == $truck->date ? '' : ($cp->date->isToday() ? 'table-info' : '') }}">

                                {{-- iterative number --}}
                                <td class="{{ $dangerClass }}">{{ transform_numeric_value($loop->iteration) }}</td>

                                {{-- fruit name --}}
                                <td class="{{ $dangerClass }}">{{ $cp->fruit_name }}</td>

                                {{-- customer --}}
                                <td class="{{ $dangerClass }}">
                                    @if ($cp->customer_name == 'نقدية' || $cp->customer_name == 'المحل')
                                        {{ $cp->customer_name }}
                                    @else
                                        <a
                                            href="{{ route('customers.show', $cp->customer->id) }}">{{ $cp->customer_name }}</a>
                                    @endif
                                </td>

                                {{-- boxes --}}
                                <td class="{{ $dangerClass }}">{{ transform_numeric_value($cp->number_of_boxes) }}
                                </td>

                                {{-- box type --}}
                                <td class="{{ $dangerClass }}">{{ $cp->box_type_name }}</td>

                                {{-- total weight --}}
                                <td class="{{ $dangerClass }}" class="fw-bolder fs-5">
                                    {{ transform_numeric_value($cp->total_weight) ?? '-' }}</td>

                                {{-- unit/box price --}}
                                <td class="{{ $dangerClass }}">
                                    {{ transform_numbers($cp->computed_unit_price ?? $cp->computed_box_price) }}
                                </td>

                                {{-- total --}}
                                <td class="{{ $dangerClass }}" class="table-light fw-bolder fs-5">
                                    {{ transform_numeric_value($cp->computed_total) }}
                                </td>

                                {{-- class name --}}
                                <td class="{{ $dangerClass }}">{{ $cp->computed_class_name }}</td>

                                {{-- actions --}}
                                <td class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editCustomerPurchaseModal{{ $cp->id }}">
                                        تعديل<i class="ps-1 bi bi-pencil-square"></i>
                                    </button>

                                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $cp->id }}"
                                        data-name="{{ $cp->customer_name }}"
                                        data-url="{{ route('customer-purchases.destroy', $cp->id) }}"
                                        data-bs-toggle="modal" data-bs-target="#deletePurchaseModal">
                                        حذف <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @push('modals')
                                @include('pages.trucks.partials.edit-customer-purchase', [
                                    'purchase' => $cp,
                                    'truck' => $truck,
                                    'customers' => $customers,
                                    'boxTypes' => $boxTypes,
                                ])
                            @endpush
                        @endforeach
                    @endforeach

                    @php
                        $grouped = collect($truck->totals_by_box_class['by_box_class'])
                            // 1️⃣ sort by boxClass key itself (ENUM order / string order)
                            ->sortKeys()
                            // 2️⃣ sort internally by price
                            ->map(function ($priceGroups) {
                                return collect($priceGroups)->sortByDesc(function ($totals) {
                                    return $totals['unit_price'] ?? $totals['box_price'];
                                });
                            });
                        $grandTotal = $truck->totals_by_box_class['total_amount'];
                        // count how many rows we will render
                        $rowCount = $grouped->flatten(1)->count();
                    @endphp

                    @foreach ($grouped as $boxClass => $priceGroups)
                        @foreach ($priceGroups as $price => $totals)
                            <tr class="table-light fw-bolder fs-5 text-center">

                                {{-- إجمالى العدد label (first cell) --}}
                                @if ($loop->parent->first && $loop->first)
                                    <td rowspan="{{ $rowCount }}" colspan="3">إجمالى العدد</td>
                                @endif

                                {{-- total boxes --}}
                                <td>
                                    {{ transform_numeric_value($totals['total_boxes']) }}
                                </td>

                                {{-- إجمالى الوزن --}}
                                @if ($loop->parent->first && $loop->first)
                                    <td rowspan="{{ $rowCount }}">إجمالى الوزن</td>
                                @endif

                                {{-- total weight --}}
                                <td
                                    class="@if ($boxClass == 'second') text-danger @elseif($boxClass == 'third') text-primary @endif">
                                    {{ transform_numeric_value($totals['total_weight'] ?? 0) }}
                                </td>

                                {{-- price --}}
                                <td
                                    class="@if ($boxClass == 'second') text-danger @elseif($boxClass == 'third') text-primary @endif">
                                    {{-- handle unit or box price --}}
                                    {{ transform_numbers($totals['unit_price'] ?? $totals['box_price']) }}
                                </td>


                                @if ($loop->parent->first && $loop->first)
                                    <td rowspan="{{ $rowCount }}" class="fw-bolder fs-3">
                                        {{ transform_numeric_value($grandTotal) }}
                                    </td>
                                @endif

                                {{-- box class label --}}
                                <td
                                    class="@if ($boxClass == 'second') text-danger @elseif($boxClass == 'third') text-primary @endif">
                                    {{ match ($boxClass) {
                                        'first' => 'فاخر',
                                        'second' => 'نمرة 2',
                                        'third' => 'نمرة 3',
                                        default => $boxClass,
                                    } }}
                                </td>

                                @if ($loop->parent->first && $loop->first)
                                    <td rowspan="{{ $rowCount }}"></td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach

                </tbody>
            </table>
        </div>
    @endif
</div>
@if (!$truck->bill && $truck->numberOfBoughtBoxes == $truck->total_boxes)
    <div class="text-center mt-4">
        <button class="btn btn-outline-success px-5" data-bs-toggle="modal" data-bs-target="#createBillModal">
            صرف الفاتورة <i class="bi bi-receipt-cutoff ps-1"></i>
        </button>
    </div>
    @push('modals')
        @include('pages.bills.partials.add-bill-modal', ['truck' => $truck])
    @endpush
@elseif ($truck->bill)
    <div class="text-center mt-4">
        <button class="btn btn-primary px-5" data-bs-toggle="modal" data-bs-target="#viewBillModal">
            عرض الفاتورة <i class="bi bi-receipt-cutoff ps-1"></i>
        </button>
    </div>
    @push('modals')
        @include('pages.bills.partials.view-bill-modal', ['truck' => $truck])
    @endpush
@endif

@push('modals')
    <x-modal id="deletePurchaseModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه المشتروات؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />
@endpush

<form id="deletePurchaseForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initDeleteHandler('purchase');

        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteForm = document.getElementById('deletePurchaseForm');

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                deleteForm.action = this.dataset.url;
            });
        });
    });
</script>
