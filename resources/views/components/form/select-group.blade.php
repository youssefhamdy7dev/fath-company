@props(['name', 'label', 'options', 'selected' => '', 'required' => false])

<div class="form-div flex-fill">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}" class="form-select @error($name) is-invalid @enderror"
        {{ $required ? 'required' : '' }}>
        <option disabled {{ !$selected ? 'selected' : '' }}>إختيار {{ $label }}...</option>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
