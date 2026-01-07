@props(['name', 'label', 'type' => 'text', 'value' => '', 'required' => false, 'dir' => 'rtl'])

<div class="form-div flex-fill">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
        class="form-control @error($name) is-invalid @enderror {{ $name == 'date' ? 'datepicker' : '' }}"
        value="{{ old($name, $value) }}" {{ $required ? 'required' : '' }} {{ $attributes }}>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
