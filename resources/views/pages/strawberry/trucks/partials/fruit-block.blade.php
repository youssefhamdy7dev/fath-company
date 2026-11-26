@php
    $index = $index ?? 0;
    $fruitData = $fruitData ?? [];
@endphp

<div class="card p-3 mb-3 fruit-block" data-index="{{ $index }}">

    {{-- IMPORTANT: Keep the database ID of the existing fruit --}}
    <input type="hidden" name="fruits[{{ $index }}][id]" value="{{ $fruitData['id'] ?? '' }}">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>الصنف رقم {{ $index + 1 }}</strong>

        <button type="button" class="btn btn-outline-danger btn-sm remove-fruit-btn">
            حذف <i class="bi bi-trash"></i>
        </button>
    </div>

    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">نوع الفراولة</label>

            <select name="fruits[{{ $index }}][fruit_type]" class="form-select">
                <option value="">اختر الصنف...</option>

                @foreach ($fruitTypes as $type)
                    <option value="{{ $type }}" @selected(($fruitData['fruit_type'] ?? '') === $type)>
                        {{ $type }}
                    </option>
                @endforeach
            </select>

            @error("fruits.$index.fruit_type")
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">العميل</label>

            <select name="fruits[{{ $index }}][client_id]" class="form-select">
                <option value="">مشتروات</option>

                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(($fruitData['client_id'] ?? '') == $client->id)>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>

            @error("fruits.$index.client_id")
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">نوع البرنيكة</label>

            <select name="fruits[{{ $index }}][box_type]" class="form-select">
                @foreach ($boxTypes as $value => $label)
                    <option value="{{ $value }}" @selected(($fruitData['box_type'] ?? 'normal_box') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @error("fruits.$index.box_type")
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">عدد الصناديق</label>

            <input type="number" min="1" name="fruits[{{ $index }}][number_of_boxes]"
                class="form-control" value="{{ $fruitData['number_of_boxes'] ?? '' }}">

            @error("fruits.$index.number_of_boxes")
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">عدد صناديق الفئة الثانية</label>

            <input type="number" name="fruits[{{ $index }}][second_class_boxes]" class="form-control"
                value="{{ $fruitData['second_class_boxes'] ?? '' }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">عدد صناديق الفئة الثالثة</label>

            <input type="number" name="fruits[{{ $index }}][third_class_boxes]" class="form-control"
                value="{{ $fruitData['third_class_boxes'] ?? '' }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">الوزن الموحد للبرنيكة (كجم)</label>

            <input type="number" min="1" step="0.01" name="fruits[{{ $index }}][unified_weight]"
                class="form-control @error("fruits.$index.unified_weight") is-invalid @enderror"
                value="{{ $fruitData['unified_weight'] ?? '' }}">

            @error("fruits.$index.unified_weight")
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">السعر الموحد للكيلو</label>

            <input type="number" min="1" step="0.01" name="fruits[{{ $index }}][unified_unit_price]"
                class="form-control @error("fruits.$index.unified_unit_price") is-invalid @enderror"
                value="{{ $fruitData['unified_unit_price'] ?? '' }}">

            @error("fruits.$index.unified_unit_price")
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">سعر البرنيكة الموحد</label>

            <input type="number" min="1" name="fruits[{{ $index }}][unified_box_price]"
                class="form-control @error("fruits.$index.unified_box_price") is-invalid @enderror"
                value="{{ $fruitData['unified_box_price'] ?? '' }}">

            @error("fruits.$index.unified_box_price")
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

    </div>
</div>
