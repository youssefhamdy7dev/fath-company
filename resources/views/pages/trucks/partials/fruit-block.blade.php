@php
    $index = $index ?? 0;
    $fruitData = $fruitData ?? [];
@endphp

<div class="card p-3 mb-3 fruit-block" data-index="{{ $index }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>الصنف رقم {{ $index + 1 }}</strong>
        <button type="button" class="btn btn-outline-danger btn-sm remove-fruit-btn">حذف<i
                class="bi bi-trash"></i></button>
    </div>

    <div class="row g-3">
        {{-- Client Selection --}}
        <div class="col-9 col-md-4">
            <label class="form-label">الصنف</label>
            <select name="fruits[{{ $index }}][fruit_id]"
                class="form-select @error("fruits.$index.fruit_id") is-invalid @enderror">
                <option disabled {{ !($fruitData['fruit_id'] ?? '') ? 'selected' : '' }}>اختر الصنف...</option>
                @foreach ($fruits as $fruit)
                    <option value="{{ $fruit->id }}"
                        {{ ($fruitData['fruit_id'] ?? '') == $fruit->id ? 'selected' : '' }}>
                        {{ $fruit->name }}
                    </option>
                @endforeach
            </select>
            @error("fruits.$index.fruit_id")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Fruit Selection --}}
        <div class="col-9 col-md-4">
            <label class="form-label">العميل</label>
            <select name="fruits[{{ $index }}][client_id]"
                class="form-select @error("fruits.$index.client_id") is-invalid @enderror">
                <option disabled {{ !($fruitData['client_id'] ?? '') ? 'selected' : '' }}>اختر العميل...</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}"
                        {{ ($fruitData['client_id'] ?? '') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
                <option value="">مشتروات</option>
            </select>
            @error("fruits.$index.client_id")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Box Type --}}
        <div class="col-9 col-md-4">
            <label class="form-label">نوع البرنيكة</label>
            <select name="fruits[{{ $index }}][box_type]"
                class="form-select @error("fruits.$index.box_type") is-invalid @enderror">
                <option disabled {{ !($fruitData['box_type'] ?? '') ? 'selected' : '' }}>إختر نوع/حجم البرنيكة...
                </option>
                @foreach ([
        'normal_box' => 'برنيكة صغيرة',
        'big_box' => 'برنيكة كبيرة',
        'small_box' => 'برنيكة 10 كيلو',
        'small_net' => 'برنيكة شبك',
    ] as $value => $label)
                    <option value="{{ $value }}"
                        {{ ($fruitData['box_type'] ?? '') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error("fruits.$index.box_type")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Box Quantities --}}
        <div class="col-12 col-md-4">
            <label class="form-label">عدد البرانيك</label>
            <input type="number" name="fruits[{{ $index }}][number_of_boxes]"
                class="form-control @error("fruits.$index.number_of_boxes") is-invalid @enderror"
                value="{{ $fruitData['number_of_boxes'] ?? '' }}">
            @error("fruits.$index.number_of_boxes")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">عدد البرانيك فئة ثانية (نمرة 2)</label>
            <input type="number" name="fruits[{{ $index }}][second_class_boxes]"
                class="form-control @error("fruits.$index.second_class_boxes") is-invalid @enderror"
                value="{{ $fruitData['second_class_boxes'] ?? '' }}">
            @error("fruits.$index.second_class_boxes")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">عدد البرانيك فئة ثالثة (نمرة 3 / هالك)</label>
            <input type="number" name="fruits[{{ $index }}][third_class_boxes]"
                class="form-control @error("fruits.$index.third_class_boxes") is-invalid @enderror"
                value="{{ $fruitData['third_class_boxes'] ?? '' }}">
            @error("fruits.$index.third_class_boxes")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Unified Values --}}
        <div class="col-12 col-md-4">
            <label class="form-label">وزن موحد (كجم)</label>
            <input type="number" step="0.01" name="fruits[{{ $index }}][unified_weight]"
                class="form-control @error("fruits.$index.unified_weight") is-invalid @enderror"
                value="{{ $fruitData['unified_weight'] ?? '' }}">
            @error("fruits.$index.unified_weight")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">السعر الموحد للكيلو</label>
            <input type="number" step="0.01" name="fruits[{{ $index }}][unified_unit_price]"
                class="form-control @error("fruits.$index.unified_unit_price") is-invalid @enderror"
                value="{{ $fruitData['unified_unit_price'] ?? '' }}">
            @error("fruits.$index.unified_unit_price")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">سعر البرنيكة الموحد</label>
            <input type="number" step="0.01" name="fruits[{{ $index }}][unified_box_price]"
                class="form-control @error("fruits.$index.unified_box_price") is-invalid @enderror"
                value="{{ $fruitData['unified_box_price'] ?? '' }}">
            @error("fruits.$index.unified_box_price")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
