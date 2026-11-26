<template id="fruitBlockTemplate">
    <div class="card p-3 mb-3 fruit-block" data-index="__INDEX__">

        {{-- New fruit: no database ID yet --}}
        <input type="hidden" name="fruits[__INDEX__][id]" value="">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>الصنف رقم __LABEL__</strong>

            <button type="button" class="btn btn-outline-danger btn-sm remove-fruit-btn">
                حذف <i class="bi bi-trash"></i>
            </button>
        </div>

        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label">نوع الفراولة</label>

                <select name="fruits[__INDEX__][fruit_type]" class="form-select">
                    <option value="">اختر الصنف...</option>

                    @foreach ($fruitTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">العميل</label>

                <select name="fruits[__INDEX__][client_id]" class="form-select">
                    <option value="">مشتروات</option>

                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">نوع البرنيكة</label>

                <select name="fruits[__INDEX__][box_type]" class="form-select">
                    @foreach ($boxTypes as $value => $label)
                        <option value="{{ $value }}" {{ $value === 'normal_box' ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">عدد الصناديق</label>

                <input type="number" min="1" name="fruits[__INDEX__][number_of_boxes]" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">عدد صناديق الفئة الثانية</label>

                <input type="number" name="fruits[__INDEX__][second_class_boxes]" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">عدد صناديق الفئة الثالثة</label>

                <input type="number" name="fruits[__INDEX__][third_class_boxes]" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">الوزن الموحد للبرنيكة (كجم)</label>

                <input type="number" min="1" step="0.01" name="fruits[__INDEX__][unified_weight]"
                    class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">السعر الموحد للكيلو</label>

                <input type="number" min="1" step="0.01" name="fruits[__INDEX__][unified_unit_price]"
                    class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">سعر البرنيكة الموحد</label>

                <input type="number" min="1" name="fruits[__INDEX__][unified_box_price]" class="form-control">
            </div>

        </div>
    </div>
</template>
