<div class="modal fade" id="truckDetailsModal" tabindex="-1" aria-labelledby="truckDetailsModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" dir="rtl">

            <div class="modal-header">
                <h5 class="modal-title" id="truckDetailsModalLabel">
                    تفاصيل أصناف العربة
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <div class="modal-body">
                @foreach ($truck->truckFruits as $index => $fruit)
                    <div class="card p-3 mb-3">

                        <h5 class="text-center fw-bold mb-3">
                            الصنف رقم {{ $index + 1 }}
                        </h5>

                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label text-secondary">نوع الفراولة</label>
                                <div class="fw-bold">{{ $fruit->fruit_type }}</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">العميل</label>
                                <div class="fw-bold">{{ $fruit->client?->name ?? 'مشتروات' }}</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">نوع البرنيكة</label>
                                <div class="fw-bold">
                                    {{ $boxTypes[$fruit->box_type] ?? $fruit->box_type }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">عدد الصناديق</label>
                                <div class="fw-bold">
                                    {{ transform_numeric_value($fruit->number_of_boxes) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">الفئة الثانية</label>
                                <div class="fw-bold text-danger">
                                    {{ transform_numeric_value($fruit->second_class_boxes) ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">الفئة الثالثة</label>
                                <div class="fw-bold">
                                    {{ transform_numeric_value($fruit->third_class_boxes) ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">الوزن الموحد للبرنيكة</label>
                                <div class="fw-bold">{{ $fruit->unified_weight ?? '-' }}</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">السعر الموحد للكيلو</label>
                                <div class="fw-bold">{{ $fruit->unified_unit_price ?? '-' }}</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">سعر البرنيكة الموحد</label>
                                <div class="fw-bold">{{ $fruit->unified_box_price ?? '-' }}</div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <div class="modal-footer justify-content-center">
                <a href="{{ route('strawberry-trucks.edit', $truck->id) }}" class="btn btn-warning">
                    تعديل <i class="bi bi-pencil-square"></i>
                </a>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    إغلاق
                </button>
            </div>

        </div>
    </div>
</div>
