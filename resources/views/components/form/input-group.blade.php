@props(['name', 'id' => null, 'label', 'type' => 'text', 'value' => '', 'required' => false, 'dir' => 'rtl'])

<div class="form-div flex-fill">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->class([
            'form-control',
            $errors->has($name) ? 'is-invalid' : '',
            $name == 'date' ? ($id == 'globdate' ? 'globdatepicker' : 'datepicker') : '',
        ]) }}>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
