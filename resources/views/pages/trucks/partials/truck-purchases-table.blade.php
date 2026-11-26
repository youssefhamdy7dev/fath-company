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
                        <th>الوزن</th>
                        <th>الفئة</th>
                        <th class="text-danger">إجمالى</th>
                        <th>النوع</th>
                        <th>تاريخ الشراء</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- Customer purchases --}}
                    @foreach ($truck->truckFruits as $tf)
                        @foreach ($tf->customerPurchases as $cp)
                            @php
                                $dangerClass = $cp->computed_class_name == 'نمرة 2' ? 'text-danger' : '';
                            @endphp

                            <tr
                                class="{{ $cp->date == $truck->date ? '' : (\App\Support\BusinessDate::isToday($cp->date) ? 'table-info' : '') }}">
                                {{-- Iterative number --}}
                                <td class="{{ $dangerClass }}">
                                    {{ transform_numeric_value($loop->iteration) }}
                                </td>

                                {{-- Fruit name --}}
                                <td class="{{ $dangerClass }}">
                                    {{ $cp->fruit_name }}
                                </td>

                                {{-- Customer --}}
                                <td class="{{ $dangerClass }}">
                                    @if ($cp->customer_name == 'نقدية' || $cp->customer_name == 'المحل')
                                        {{ $cp->customer_name }}
                                    @else
                                        <a href="{{ route('customers.show', $cp->customer->id) }}">
                                            {{ $cp->customer_name }}
                                        </a>
                                    @endif
                                </td>

                                {{-- Boxes --}}
                                <td class="{{ $dangerClass }}">
                                    {{ transform_numeric_value($cp->number_of_boxes) }}
                                </td>

                                {{-- Total weight --}}
                                <td class="{{ $dangerClass }}">
                                    {{ transform_numeric_value($cp->total_weight) ?? '-' }}
                                </td>

                                {{-- Unit/box price --}}
                                <td class="{{ $dangerClass }}">
                                    {{ transform_numbers($cp->computed_unit_price ?? $cp->computed_box_price) }}
                                </td>

                                {{-- Total --}}
                                <td class="{{ $dangerClass }} fw-bolder fs-6">
                                    {{ transform_numeric_value($cp->computed_total) }}
                                </td>

                                {{-- Class name --}}
                                <td class="{{ $dangerClass }}">
                                    {{ $cp->computed_class_name }}
                                </td>

                                {{-- Date --}}
                                <td class="{{ $dangerClass }}">
                                    {{ transform_numbers($cp->date->locale('ar')->translatedFormat('l Y/m/d')) }}
                                </td>

                                {{-- Actions --}}
                                <td class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editCustomerPurchaseModal{{ $cp->id }}">
                                        تعديل <i class="ps-1 bi bi-pencil-square"></i>
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

                    {{-- Totals for multiple fruits --}}
                    @if ($truck->truckFruits->count() > 1)
                        @php
                            $grandBoxes = 0;
                            $grandWeight = 0;
                            $grandAmount = 0;
                        @endphp

                        @foreach ($truck->truckFruits as $tf)
                            @php
                                $fruitBoxes = 0;
                                $fruitWeight = 0;
                                $fruitAmount = 0;

                                $grouped = $tf->customerPurchases->groupBy('box_class')->map(function ($classGroup) {
                                    return $classGroup
                                        ->groupBy(function ($cp) {
                                            if ($cp->computed_unit_price !== null) {
                                                return 'unit_' . $cp->computed_unit_price;
                                            }

                                            return 'box_' . $cp->computed_box_price;
                                        })
                                        ->sortByDesc(function ($priceGroup) {
                                            $first = $priceGroup->first();

                                            return $first->computed_unit_price ?? $first->computed_box_price;
                                        });
                                });
                            @endphp

                            <tr class="table-primary fs-5 fw-bold">
                                <td colspan="10">
                                    {{ $tf->fruit->name }}
                                </td>
                            </tr>

                            @foreach ($grouped as $boxClass => $priceGroups)
                                @foreach ($priceGroups as $priceGroup)
                                    @php
                                        $boxes = $priceGroup->sum('number_of_boxes');
                                        $weight = $priceGroup->sum(fn($p) => $p->total_weight ?? 0);
                                        $amount = $priceGroup->sum('computed_total');

                                        $fruitBoxes += $boxes;
                                        $fruitWeight += $weight;
                                        $fruitAmount += $amount;

                                        $classColor = match ($boxClass) {
                                            'second' => 'text-danger',
                                            'third' => 'text-primary',
                                            default => '',
                                        };
                                    @endphp

                                    <tr class="table-light fw-bolder text-center">
                                        <td colspan="3"></td>

                                        <td class="{{ $classColor }}">
                                            {{ transform_numeric_value($boxes) }}
                                        </td>

                                        <td class="{{ $classColor }}">
                                            {{ transform_numeric_value($weight) }}
                                        </td>

                                        <td class="{{ $classColor }}">
                                            {{ transform_numbers($priceGroup->first()->computed_unit_price ?? $priceGroup->first()->computed_box_price) }}
                                        </td>

                                        <td class="{{ $classColor }}">
                                            {{ transform_numeric_value($amount) }}
                                        </td>

                                        <td class="{{ $classColor }}">
                                            {{ match ($boxClass) {
                                                'first' => 'فاخر',
                                                'second' => 'نمرة 2',
                                                'third' => 'نمرة 3',
                                                default => $boxClass,
                                            } }}
                                        </td>

                                        <td colspan="2"></td>
                                    </tr>
                                @endforeach
                            @endforeach

                            {{-- Fruit totals --}}
                            <tr class="table-warning fw-bold">
                                <td colspan="3">إجمالى الصنف</td>

                                <td>{{ transform_numeric_value($fruitBoxes) }}</td>
                                <td>{{ transform_numeric_value($fruitWeight) }}</td>
                                <td></td>
                                <td>{{ transform_numeric_value($fruitAmount) }}</td>

                                <td colspan="3"></td>
                            </tr>

                            @php
                                $grandBoxes += $fruitBoxes;
                                $grandWeight += $fruitWeight;
                                $grandAmount += $fruitAmount;
                            @endphp
                        @endforeach

                        {{-- Grand totals --}}
                        <tr class="table-dark fw-bold fs-5">
                            <td colspan="3">الإجمالى العام</td>

                            <td>{{ transform_numeric_value($grandBoxes) }}</td>
                            <td>{{ transform_numeric_value($grandWeight) }}</td>
                            <td></td>
                            <td>{{ transform_numeric_value($grandAmount) }}</td>

                            <td colspan="3"></td>
                        </tr>
                    @else
                        {{-- Totals for a single fruit --}}
                        @php
                            $grouped = collect($truck->totals_by_box_class['by_box_class'])
                                ->sortKeys()
                                ->map(function ($priceGroups) {
                                    return collect($priceGroups)->sortByDesc(function ($totals) {
                                        return $totals['unit_price'] ?? $totals['box_price'];
                                    });
                                });

                            $grandTotal = $truck->totals_by_box_class['total_amount'];
                            $rowCount = $grouped->flatten(1)->count();
                        @endphp

                        @foreach ($grouped as $boxClass => $priceGroups)
                            @foreach ($priceGroups as $price => $totals)
                                @php
                                    $classColor = match ($boxClass) {
                                        'second' => 'text-danger',
                                        'third' => 'text-primary',
                                        default => '',
                                    };
                                @endphp

                                <tr class="table-light fw-bolder fs-5 text-center">
                                    @if ($loop->parent->first && $loop->first)
                                        <td rowspan="{{ $rowCount }}" colspan="3">إجمالى العربة</td>
                                    @endif

                                    <td>
                                        {{ transform_numeric_value($totals['total_boxes']) }}
                                    </td>

                                    <td class="{{ $classColor }}">
                                        {{ transform_numeric_value($totals['total_weight'] ?? 0) }}
                                    </td>

                                    <td class="{{ $classColor }}">
                                        {{ transform_numbers($totals['unit_price'] ?? $totals['box_price']) }}
                                    </td>

                                    @if ($loop->parent->first && $loop->first)
                                        <td rowspan="{{ $rowCount }}" class="fw-bolder fs-3">
                                            {{ transform_numeric_value($grandTotal) }}
                                        </td>
                                    @endif

                                    <td class="{{ $classColor }}">
                                        {{ match ($boxClass) {
                                            'first' => 'فاخر',
                                            'second' => 'نمرة 2',
                                            'third' => 'نمرة 3',
                                            default => $boxClass,
                                        } }}
                                    </td>

                                    @if ($loop->parent->first && $loop->first)
                                        <td rowspan="{{ $rowCount }}"></td>
                                        <td rowspan="{{ $rowCount }}"></td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Bill actions --}}
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

{{-- Purchase deletion modal --}}
@push('modals')
    <x-modal id="deletePurchaseModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه المشتروات؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />
@endpush

{{-- Hidden delete form --}}
<form id="deletePurchaseForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

{{-- Purchase deletion handler --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initDeleteHandler('purchase');

        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteForm = document.getElementById('deletePurchaseForm');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                deleteForm.action = this.dataset.url;
            });
        });
    });
</script>
