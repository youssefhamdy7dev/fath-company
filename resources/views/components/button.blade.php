@props([
    'href' => null,
    'type' => 'button',
    'color' => 'primary',
])

@if ($href)
    <a {{ $attributes->merge(['href' => $href, 'class' => "btn btn-$color"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "btn btn-$color"]) }}>
        {{ $slot }}
    </button>
@endif
