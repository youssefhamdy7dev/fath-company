<div class="card card-background mb-3" dir="rtl">

    <div class="d-flex justify-content-between w-100 mb-3">
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#truckDetailsModal">
            <span class="fw-bolder">تفاصيل</span>
            <i class="ps-1 bi bi-card-list"></i>
        </button>
        <div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerPurchaseModal">
                إضافة مشتروات الزبائن
                <i class="ps-1 bi bi-card-checklist"></i>
            </button>

            <a href="{{ route('strawberry-trucks.index') }}" class="btn btn-info">
                العودة إلى قائمة العربات
                <i class="bi bi-truck"></i>
            </a>
        </div>
    </div>
    <div class="fs-3 text-center mb-3"><strong class="badge bg-danger">فراولة</strong></div>

    <div class="row text-center fw-bold fs-6">

        <div class="col-md-4 mb-3">
            <span class="text-secondary d-block">الصنف / الأصناف</span>
            {{ $truck->fruit_names }}
        </div>

        <div class="col-md-4 mb-3">
            <span class="text-secondary d-block">عدد الصناديق</span>
            {{ transform_numeric_value($truck->total_boxes) }}
        </div>

        <div class="col-md-4 mb-3">
            <span class="text-secondary d-block">العميل</span>
            {{ $truck->client_names ?? 'مشتروات' }}
        </div>

        <div class="col-md-4 mb-3">
            <span class="text-secondary d-block">التاريخ</span>
            {{ transform_numbers($truck->date->format('d-m-Y')) }}
        </div>

        @if ($truck->total_second_class_boxes > 0)
            <div class="col-md-4 mb-3">
                <span class="text-secondary d-block">عدد صناديق الفئة الثانية</span>
                <span class="text-danger">
                    {{ transform_numeric_value($truck->total_second_class_boxes) }}
                </span>
            </div>
        @endif

        <div class="col-md-4 mb-3">
            <span class="text-secondary d-block">السائق</span>
            {{ $truck->driver?->name ?? '—' }}
        </div>

        @if ($truck->total_third_class_boxes > 0)
            <div class="col-md-4 mb-3">
                <span class="text-secondary d-block">عدد صناديق الفئة الثالثة</span>
                {{ transform_numeric_value($truck->total_third_class_boxes) }}
            </div>
        @endif

        <div class="col-md-4 mb-3 mx-auto">
            <span class="text-secondary d-block">النولون</span>
            {{ transform_numeric_value($truck->freight) }}
        </div>

        <div class="d-flex justify-content-between">
            <div class="d-flex gap-2">
                <x-status-chip type="numbers" value="info" :number="$truck->numberOfBoughtBoxes" />

                @php
                    $remaining = $truck->total_boxes - $truck->numberOfBoughtBoxes;
                @endphp

                @if ($remaining > 0)
                    <x-status-chip type="numbers" value="positive" :number="$remaining" />
                @elseif ($remaining < 0)
                    <x-status-chip type="numbers" value="negative" :number="$remaining" />
                @endif
            </div>

            <x-status-chip type="completed" :value="$truck->numberOfBoughtBoxes >= $truck->total_boxes" />
        </div>
    </div>
</div>
