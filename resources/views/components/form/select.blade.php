@props(['id', 'label', 'name', 'options' => [], 'selected' => null])

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}"
        {{ $attributes->merge(['class' => 'form-select ' . ($errors->has($name) ? 'is-invalid' : '')]) }}>
        <option disabled selected>اختر {{ strtolower($label) }} ...</option>
        @foreach ($options as $option)
            <option value="{{ $option }}" {{ old($name, $selected) == $option ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
