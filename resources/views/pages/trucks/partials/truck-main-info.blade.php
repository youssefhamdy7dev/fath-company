    <div class="card card-background mb-3" dir="rtl">
        <div class="d-flex justify-content-between w-100 mb-3">
            {{-- Details Modal Button --}}
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#truckDetailsModal">
                <span class="fw-bolder">تفاصيل</span><i class="ps-1 bi bi-card-list"></i>
            </button>

            <div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerPurchaseModal">
                    إضافة مشتروات الزبائن
                    <i class="ps-1 bi bi-card-checklist"></i>
                </button>
                <button class="btn btn-info" onclick="location.href='{{ route('trucks.index') }}'">
                    العودة إلى قائمة العربات <i class="bi bi-truck"></i>
                </button>
            </div>
        </div>

        <div class="row text-center fw-bold fs-6">
            <div class="col-md-4 mb-3">
                <span class="text-secondary d-block">الصنف/الأصناف</span>
                {{ $truck->fruit_names }}
            </div>

            <div class="col-md-4 mb-3">
                <span class="text-secondary d-block">عدد البرانيك</span>
                <span>{{ transform_numeric_value($truck->total_boxes) }}</span>
            </div>

            <div class="col-md-4 mb-3">
                <span class="text-secondary d-block">العميل</span>
                <span>{{ $truck->client_names ?? 'مشتروات' }}</span>
            </div>

            <div class="col-md-4 mb-3">
                <span class="text-secondary d-block">التاريخ</span>
                <span>{{ transform_numbers(\Carbon\Carbon::parse($truck->date)->format('d-m-Y')) }}</span>
            </div>

            @if ($truck->total_second_class_boxes > 0)
                <div class="col-md-4 mb-3">
                    <span class="text-secondary d-block">عدد برانيك الفئة الثانية</span>
                    <span class="text-danger">{{ transform_numeric_value($truck->total_second_class_boxes) }}</span>
                </div>
            @endif

            <div class="col-md-4 mb-3">
                <span class="text-secondary d-block">السائق</span>
                <span>{{ $truck->driver?->name ?? '—' }}</span>
            </div>

            @if ($truck->total_third_class_boxes > 0)
                <div class="mb-3 mx-auto">
                    <span class="text-secondary d-block">عدد برانيك الفئة الثالثة</span>
                    <span>{{ transform_numeric_value($truck->total_third_class_boxes) }}</span>
                </div>
            @endif

            <div class="col-md-4 mb-3 mx-auto">
                <span class="text-secondary d-block">النولون</span>
                <span>{{ transform_numeric_value($truck->freight) }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <div class="d-flex justify-content-between gap-2">
                    @php
                        $difference = $truck->total_boxes - $truck->numberOfBoughtBoxes;
                    @endphp
                    <x-status-chip type="numbers" value="info" :number="$truck->numberOfBoughtBoxes" />
                    @if ($difference != 0)
                        @if ($difference > 0 && $difference <= $truck->total_boxes)
                            <x-status-chip type="numbers" value="positive" :number="$difference" />
                        @elseif($difference < 0)
                            <x-status-chip type="numbers" value="negative" :number="$difference" />
                        @endif
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <x-status-chip type="billed" :value="$truck->bill ? true : false" />
                    <x-status-chip type="completed" :value="$truck->numberOfBoughtBoxes == $truck->total_boxes" />
                </div>
            </div>
        </div>
    </div>
