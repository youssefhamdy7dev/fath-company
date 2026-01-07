@props(['active' => false, 'href'])

@php
    $classes = $active ? 'nav-link active fw-bold text-white' : 'nav-link text-secondary';
@endphp

<a {{ $attributes->merge(['href' => $href, 'class' => $classes]) }}>
    {{ $slot }}
</a>
