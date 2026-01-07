<template id="fruitBlockTemplate">
    <div class="card p-3 mb-3 fruit-block" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>الصنف رقم __LABEL__</strong>
            <button type="button" class="btn btn-outline-danger btn-sm remove-fruit-btn">حذف<i
                    class="bi bi-trash"></i></button>
        </div>

        <div class="row g-3">
            <div class="col-9 col-md-4">
                <label class="form-label">الصنف</label>
                <select name="fruits[__INDEX__][fruit_id]" class="form-select">
                    <option disabled selected>اختر الصنف...</option>
                    @foreach ($fruits as $fruit)
                        <option value="{{ $fruit->id }}">{{ $fruit->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-9 col-md-4">
                <label class="form-label">العميل</label>
                <select name="fruits[__INDEX__][client_id]" class="form-select">
                    <option disabled selected>اختر العميل...</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                    <option value="">مشتروات</option>
                </select>
            </div>

            <div class="col-9 col-md-4">
                <label class="form-label">نوع البرنيكة</label>
                <select name="fruits[__INDEX__][box_type]" class="form-select">
                    <option disabled selected>إختر نوع/حجم البرنيكة...</option>
                    <option value="normal_box">برنيكة صغيرة</option>
                    <option value="big_box">برنيكة كبيرة</option>
                    <option value="small_box">برنيكة 10 كيلو</option>
                    <option value="small_net">برنيكة شبك</option>
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">عدد البرانيك</label>
                <input type="number" min="0" name="fruits[__INDEX__][number_of_boxes]" class="form-control">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">عدد البرانيك فئة ثانية (نمرة 2)</label>
                <input type="number" min="0" name="fruits[__INDEX__][second_class_boxes]" class="form-control">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">عدد البرانيك فئة ثالثة (نمرة 3 / هالك)</label>
                <input type="number" min="0" name="fruits[__INDEX__][third_class_boxes]" class="form-control">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">وزن موحد (كجم)</label>
                <input type="number" min="0" step="0.01" name="fruits[__INDEX__][unified_weight]"
                    class="form-control">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">السعر الموحد للكيلو</label>
                <input type="number" min="0" step="0.01" name="fruits[__INDEX__][unified_unit_price]"
                    class="form-control">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">سعر البرنيكة الموحد</label>
                <input type="number" min="0" step="0.01" name="fruits[__INDEX__][unified_box_price]"
                    class="form-control">
            </div>
        </div>
    </div>
</template>
