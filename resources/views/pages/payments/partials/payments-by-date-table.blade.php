@if ($grouped->isEmpty())
    <p class="text-center fw-bolder fs-5 text-muted">لا يوجد</p>
@else
    <div class="table-responsive">
        <table class="table table-secondary table-bordered table-hover text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th class="text-danger fw-bolder fs-5">إجمالى حساب الزبون</th>
                    <th>الزبون</th>
                    <th class="text-success fw-bolder fs-5">التحصيل</th>
                    <th>الخصم</th>
                    <th class="text-primary fw-bolder fs-5">الباقى</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach ($grouped as $location => $payments)
                    <tr class="table-info">
                        <td colspan="7" class="fw-bold">{{ $location }}</td>
                    </tr>
                    @foreach ($payments as $payment)
                        @php
                            $remaining =
                                $payment->customer->getBalanceBefore($displayDate) -
                                (($payment->amount ?? 0) + ($payment->discount ?? 0));
                        @endphp
                        <tr>
                            <td>{{ transform_numeric_value($i++) }}</td>
                            <td class="text-danger fw-bolder fs-5">
                                {{ transform_numeric_value($payment->customer->getBalanceBefore($displayDate)) }}</td>
                            <td><a href="{{ route('customers.show', $payment->customer->id) }}">
                                    {{ $payment->customer->name }}</a></td>
                            <td class="text-success fw-bolder fs-5">{{ transform_numeric_value($payment->amount) }}</td>
                            <td>
                                @if ($payment->discount === null)
                                    <span class="text-muted">-</span>
                                @else
                                    {{ transform_numeric_value($payment->discount) }}
                                @endif
                            </td>
                            <td class="text-primary fw-bolder fs-5">
                                {{ transform_numeric_value($remaining) }}
                            </td>
                            <td class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editCustomerPaymentModal{{ $payment->id }}">
                                    تعديل<i class="ps-1 bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $payment->id }}"
                                    data-name="{{ $payment->customer->name }}"
                                    data-url="{{ route('customer-payments.destroy', $payment->id) }}"
                                    data-bs-toggle="modal" data-bs-target="#deletePaymentModal">
                                    حذف <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @push('modals')
                            @include('pages.payments.partials.edit-payment-modal', [
                                'payment' => $payment,
                            ])
                        @endpush
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@push('modals')
    <x-modal id="deletePaymentModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذه المشتروات؟" confirmText="تأكيد"
        cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />
@endpush

<form id="deletePaymentForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initDeleteHandler('payment');

        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteForm = document.getElementById('deletePaymentForm');

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                deleteForm.action = this.dataset.url;
            });
        });
    });
</script>
