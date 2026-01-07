@props(['title', 'icon', 'link', 'color' => 'primary', 'count' => null])

<div class="card-background col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="card text-center shadow-sm border-0 p-4 bg-light h-100">
        <div class="mb-3">
            <i class="bi {{ $icon }} text-{{ $color }}" style="font-size: 2.8rem;"></i>
        </div>
        <h5 class="fw-bold mb-2">{{ $title }}</h5>

        @if ($count !== null)
            <p class="text-muted mb-3">عدد السجلات: <strong>{{ $count }}</strong></p>
        @endif

        <a href="{{ $link }}" class="btn btn-{{ $color }} w-100">
            إدارة {{ $title }}
        </a>
    </div>
</div>
