<div class="d-flex gap-3">
    <x-form.input-group name="date" label="التاريخ" type="text" value="{{ old('date') }}" class="datepicker"
        autocomplete="off" />

    <x-form.input-group name="total_boxes" label="إجمالى عدد البرانيك" type="number" min="0"
        value="{{ old('total_boxes') }}" />
</div>

<div class="d-flex gap-3">
    <x-form.select-group name="driver_id" label="السائق" :options="$drivers->pluck('name', 'id')" selected="{{ old('driver_id') }}" />

    <x-form.input-group name="freight" label="النولون" type="number" min="0" step="0.01"
        value="{{ old('freight') }}" />
</div>
