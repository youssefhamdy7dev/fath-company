@forelse ($customersByPlace as $placeKey => $customers)

    <div class="card shadow-sm mb-4" data-place-group>

        {{-- Main Place Heading (unchanged) --}}
        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">
                {{ $placeKey }}
            </h5>

        </div>

        <div class="card-body">

            @if ($customers->isEmpty())
                <div class="alert alert-info text-center mb-0">
                    لا يوجد زبائن في هذه المجموعة.
                </div>
            @else
                <div class="table-responsive">

                    <table class="table table-secondary table-bordered table-hover text-center align-middle mb-0">

                        {{-- Table Headers --}}
                        <thead class="table-secondary">

                            <tr>
                                <th>حساب الزبون</th>
                                <th>اسم الزبون</th>
                                <th>المنطقة</th>
                                <th class="text-success fw-bold">التحصيل</th>
                                <th class="text-danger fw-bold">ما تبقى بعد التحصيل</th>
                            </tr>

                        </thead>

                        {{-- Table Body --}}
                        <tbody>

                            @php
                                $currentLocation = null;
                            @endphp

                            @foreach ($customers as $customer)
                                {{-- New Location Group Heading --}}
                                @if ($currentLocation !== $customer->location)
                                    @php
                                        $currentLocation = $customer->location;
                                    @endphp

                                    <tr class="table-primary">
                                        <td colspan="5" class="fw-bold text-center">
                                            {{ $currentLocation ?: 'بدون منطقة' }}
                                        </td>
                                    </tr>
                                @endif

                                @php
                                    $payment = $paymentsByCustomer->get($customer->id);

                                    $balanceBefore = (int) $balancesByCustomer->get(
                                        $customer->id,
                                        (int) $customer->account,
                                    );

                                    $paymentAmount = (int) ($payment->amount ?? 0);
                                    $discountAmount = (int) ($payment->discount ?? 0);

                                    $remaining = $balanceBefore - $paymentAmount - $discountAmount;
                                @endphp

                                <tr data-customer-row data-customer-name="{{ $customer->name }}">

                                    {{-- Customer Balance Before Collection --}}
                                    <td>
                                        {{ transform_numeric_value($balanceBefore) }}
                                    </td>

                                    {{-- Customer Name --}}
                                    <td>
                                        <a href="{{ route('customers.show', $customer->id) }}"
                                            class="text-decoration-none">
                                            {{ $customer->name }}
                                        </a>
                                    </td>

                                    {{-- Location --}}
                                    <td>
                                        {{ $customer->location }}
                                    </td>

                                    {{-- Payment --}}
                                    <td class="text-success fw-bold">
                                        @if ($paymentAmount > 0)
                                            {{ transform_numeric_value($paymentAmount) }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- Remaining --}}
                                    <td class="text-danger fw-bold">
                                        {{ transform_numeric_value($remaining) }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>
            @endif

        </div>

    </div>

@empty

    <div class="alert alert-warning text-center">
        لم يتم اختيار أي مجموعة.
    </div>
@endforelse
