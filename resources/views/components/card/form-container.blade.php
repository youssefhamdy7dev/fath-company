@props(['title'])

<div class="d-flex justify-content-center align-items-center">
    <div class="card card-background shadow-sm p-4" style="width: 100%; max-width: 500px;">
        <h2 class="text-center mb-4">{{ $title }}</h2>
        {{ $slot }}
    </div>
</div>
