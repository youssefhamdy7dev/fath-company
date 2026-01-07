@props(['type', 'number' => null, 'value' => false])

@php
    $config = [
        'billed' => [
            true => ['text' => 'صرفت', 'class' => 'chip chip-lime'],
            false => ['text' => 'لم تصرف', 'class' => 'chip chip-danger'],
        ],
        'completed' => [
            true => ['text' => 'خالص', 'class' => 'chip chip-primary'],
            false => ['text' => 'جارى التوزيع', 'class' => 'chip chip-warning'],
        ],
        'numbers' => [
            'positive' => [
                'text' => 'باقى ' . (transform_numbers($number) ?? 0),
                'class' => 'chip text-secondary',
            ],
            'negative' => [
                'text' => 'زيادة ' . (transform_numbers($number) ?? 0),
                'class' => 'chip text-success',
            ],
            'info' => [
                'text' => 'تم توزيع ' . (transform_numbers($number) ?? 0),
                'class' => 'chip text-info',
            ],
        ],
    ];

    $chip = $config[$type][$value];
@endphp

<span class="{{ $chip['class'] }}">{{ $chip['text'] }}</span>
