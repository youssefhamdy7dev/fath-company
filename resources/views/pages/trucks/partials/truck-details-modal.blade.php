<div class="modal fade" id="truckDetailsModal" tabindex="-1" aria-labelledby="truckDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <div class="modal-header">
                <h5 class="modal-title" id="truckDetailsModalLabel">تفاصيل أصناف العربة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">

                @foreach ($truck->truckFruits as $idx => $truckFruit)
                    <div class="card p-3 mb-3">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <h4 class="fw-bolder">الصنف رقم {{ $idx + 1 }}</h4>
                        </div>

                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">الصنف</label>
                                <div class="fw-bolder">{{ $truckFruit->fruit->name }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">العميل</label>
                                <div class="fw-bolder">{{ $truckFruit->client?->name ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">نوع البرنيكة</label>
                                <div class="fw-bolder">{{ $boxTypes[$truckFruit->box_type] }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">عدد البرانيك</label>
                                <div class="fw-bolder">{{ $truckFruit->number_of_boxes }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">عدد البرانيك فئة ثانية</label>
                                <div class="fw-bolder text-danger">{{ $truckFruit->second_class_boxes ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">عدد البرانيك فئة ثالثة</label>
                                <div class="fw-bolder">{{ $truckFruit->third_class_boxes ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">وزن موحد (كجم)</label>
                                <div class="fw-bolder">{{ $truckFruit->unified_weight ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">السعر الموحد للكيلو</label>
                                <div class="fw-bolder">{{ $truckFruit->unified_unit_price ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-secondary">سعر البرنيكة الموحد</label>
                                <div class="fw-bolder">{{ $truckFruit->unified_box_price ?? '-' }}</div>
                            </div>

                        </div>
                    </div>
                @endforeach

            </div>

            <div class="modal-footer justify-content-center">
                <a href="{{ route('trucks.edit', $truck->id) }}" class="btn btn-warning">
                    تعديل <i class="bi bi-pencil-square"></i>
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>

        </div>
    </div>
</div>
