@props(['id', 'label', 'name'])

<div class="form-check mb-3">
    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" {{ old($name) ? 'checked' : '' }}
        class="form-check-input">
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
</div>
