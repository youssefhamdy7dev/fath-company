@php
    // Combine the selected groups and regroup by actual customer location.
    // Payments without a customer are grouped under نقدية.
    $paymentsByLocation = $grouped->flatten(1)->groupBy(fn($payment) => $payment->customer?->location ?? 'نقدية');

    // Match the location order used in the customers pages.
    $locationOrder = \App\Support\CustomerLocations::all();

    $paymentsByLocation = $paymentsByLocation->sortBy(function ($payments, $location) use ($locationOrder) {
        if ($location === 'نقدية') {
            return PHP_INT_MAX;
        }

        $position = array_search($location, $locationOrder, true);

        return $position === false ? PHP_INT_MAX - 1 : $position;
    });
@endphp

@if ($paymentsByLocation->isEmpty())
    <p class="text-center fw-bolder fs-5 text-muted">
        لا يوجد
    </p>
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
                @php
                    $i = 1;
                @endphp

                @foreach ($paymentsByLocation as $location => $payments)
                    {{-- Location heading --}}
                    <tr class="table-info">
                        <td colspan="7" class="fw-bold">
                            {{ $location }}
                        </td>
                    </tr>

                    @foreach ($payments as $payment)
                        @php
                            $isCash = $payment->customer_id === null;

                            $balanceBefore = $isCash ? null : $balancesByCustomer[$payment->customer_id] ?? 0;

                            $remaining = $isCash
                                ? null
                                : $balanceBefore - (($payment->amount ?? 0) + ($payment->discount ?? 0));
                        @endphp

                        <tr>
                            <td>
                                {{ transform_numeric_value($i++) }}
                            </td>

                            {{-- Previous Strawberry balance --}}
                            <td class="text-danger fw-bolder fs-5">
                                @if ($isCash)
                                    <span class="text-muted">-</span>
                                @else
                                    {{ transform_numeric_value($balanceBefore) }}
                                @endif
                            </td>

                            {{-- Customer / Cash --}}
                            <td>
                                @if ($isCash)
                                    <span class="fw-bold">نقدية</span>
                                @else
                                    <a href="{{ route('customers.show', $payment->customer->id) }}">
                                        {{ $payment->customer->name }}
                                    </a>
                                @endif

                                @if ($payment->notes)
                                    <div class="small text-muted mt-1">
                                        {{ $payment->notes }}
                                    </div>
                                @endif
                            </td>

                            {{-- Payment amount --}}
                            <td class="text-success fw-bolder fs-5">
                                {{ transform_numeric_value($payment->amount) }}
                            </td>

                            {{-- Discount --}}
                            <td>
                                @if ($payment->discount === null)
                                    <span class="text-muted">-</span>
                                @else
                                    {{ transform_numeric_value($payment->discount) }}
                                @endif
                            </td>

                            {{-- Remaining balance --}}
                            <td class="text-primary fw-bolder fs-5">
                                @if ($isCash)
                                    <span class="text-muted">-</span>
                                @else
                                    {{ transform_numeric_value($remaining) }}
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editStrawberryCustomerPaymentModal{{ $payment->id }}">
                                    تعديل
                                    <i class="ps-1 bi bi-pencil-square"></i>
                                </button>

                                <button type="button" class="btn btn-danger btn-sm delete-btn"
                                    data-id="{{ $payment->id }}"
                                    data-name="{{ $payment->customer?->name ?? 'نقدية' }}"
                                    data-url="{{ route('strawberry-customer-payments.destroy', $payment->id) }}"
                                    data-bs-toggle="modal" data-bs-target="#deleteStrawberryPaymentModal">
                                    حذف
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>

                        @push('modals')
                            @include('pages.strawberry.payments.partials.edit-payment-modal', [
                                'payment' => $payment,
                                'customers' => $customers,
                            ])
                        @endpush
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@push('modals')
    <x-modal id="deleteStrawberryPaymentModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا التحصيل؟"
        confirmText="تأكيد" cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />
@endpush

<form id="deleteStrawberryPaymentForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initDeleteHandler('strawberry-payment');

        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteForm = document.getElementById('deleteStrawberryPaymentForm');

        if (!deleteForm) return;

        deleteButtons.forEach(button => {
            if (button.dataset.deleteActionBound === 'true') return;

            button.dataset.deleteActionBound = 'true';

            button.addEventListener('click', function() {
                deleteForm.action = this.dataset.url;
            });
        });
    });
</script>
