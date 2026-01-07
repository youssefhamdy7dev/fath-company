@props([
    'id',
    'title' => 'Modal Title',
    'body' => '',
    'confirmText' => 'تأكيد',
    'cancelText' => 'إلغاء',
    'confirmButtonClass' => 'btn-primary',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" dir="rtl">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                {{ $body }}
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn {{ $confirmButtonClass }}"
                    id="{{ $id }}Confirm">{{ $confirmText }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $cancelText }}</button>
            </div>
        </div>
    </div>
</div>
