@props(['type' => session('success') ? 'success' : (session('error') ? 'error' : null)])

@if ($type)
    <div class="d-flex justify-content-center align-content-center text-center">
        <div class="alert alert-{{ $type === 'success' ? 'success' : 'danger' }} alert-dismissible fade show animated-alert"
            role="alert">
            <i class="bi {{ $type === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
            <strong>
                {{ $type === 'success' ? session('success') : session('error') }}
            </strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif
