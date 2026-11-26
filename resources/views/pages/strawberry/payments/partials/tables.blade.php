@forelse ($customersByPlace as $placeKey => $customers)

    @if (!$customers->isEmpty())
        <div class="card shadow-sm mb-4" data-place-group>

            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    {{ $placeKey }}
                </h5>
            </div>

            <div class="card-body">


                <div class="table-responsive">

                    <table class="table table-danger table-bordered table-hover text-center align-middle mb-0">

                        <thead class="table-secondary">
                            <tr>
                                <th>حساب الزبون</th>
                                <th>اسم الزبون</th>
                                <th>المنطقة</th>
                                <th class="text-success fw-bold">التحصيل</th>
                                <th class="text-warning fw-bold">الخصم</th>
                                <th class="text-danger fw-bold">ما تبقى بعد التحصيل</th>
                            </tr>
                        </thead>

                        <tbody>

                            @php
                                $currentLocation = null;
                            @endphp

                            @foreach ($customers as $customer)
                                @if ($currentLocation !== $customer->location)
                                    @php
                                        $currentLocation = $customer->location;
                                    @endphp

                                    <tr class="table-primary">
                                        <td colspan="6" class="fw-bold text-center">
                                            {{ $currentLocation ?: 'بدون منطقة' }}
                                        </td>
                                    </tr>
                                @endif

                                @php
                                    $payment = $paymentsByCustomer->get($customer->id);

                                    // Strawberry balance starts at zero.
                                    // Do not use the normal customer account.
                                    $balanceBefore = (int) $balancesByCustomer->get($customer->id, 0);

                                    $paymentAmount = (int) ($payment->amount ?? 0);
                                    $discountAmount = (int) ($payment->discount ?? 0);

                                    $remaining = $balanceBefore - $paymentAmount - $discountAmount;
                                @endphp

                                <tr data-customer-row data-customer-name="{{ $customer->name }}">

                                    <td>
                                        {{ transform_numeric_value($balanceBefore) }}
                                    </td>

                                    <td>
                                        <a href="{{ route('customers.show', $customer->id) }}"
                                            class="text-decoration-none">
                                            {{ $customer->name }}
                                        </a>
                                    </td>

                                    <td>
                                        {{ $customer->location }}
                                    </td>

                                    <td class="text-success fw-bold">
                                        @if ($paymentAmount > 0)
                                            {{ transform_numeric_value($paymentAmount) }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- Discount --}}
                                    <td class="text-warning fw-bold">
                                        @if ($discountAmount > 0)
                                            {{ transform_numeric_value($discountAmount) }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-danger fw-bold">
                                        {{ transform_numeric_value($remaining) }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    @endif

@empty

    <div class="alert alert-warning text-center">
        لم يتم اختيار أي مجموعة.
    </div>

@endforelse
