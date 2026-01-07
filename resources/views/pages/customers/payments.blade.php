@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4">

        {{-- Back --}}
        <div class="d-flex justify-content-end gap-2 w-100 mb-3">
            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-dark">
                العودة لبيانات الزبون <i class="bi bi-arrow-up-left"></i>
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-info">
                العودة إلى قائمة الزبائن <i class="bi bi-person-lines-fill"></i>
            </a>
        </div>

        {{-- Title --}}
        <h2 class="text-center text-muted mb-4">
            سجل تحصيل الزبون: {{ $customer->name }}
        </h2>

        {{-- Payments Table --}}
        <div class="table-responsive">
            <table class="table table-secondary table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>حساب الزبون فى هذا اليوم</th>
                        <th>التحصيل</th>
                        <th>الخصم</th>
                        <th>الباقى</th>
                        <th>تاريخ التحصيل</th>
                        <th>إجراء</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($payments as $index => $payment)
                        @php
                            $customerBalance = $payment->customer->getBalanceBefore($payment->date);
                            $remaining = $customerBalance - ($payment->amount + ($payment->discount ?? 0));
                        @endphp
                        <tr>
                            <td>{{ transform_numeric_value($loop->iteration) }}</td>

                            <td class="fs-5 text-danger fw-bolder">
                                {{ transform_numeric_value($customerBalance) }}</td>

                            <td class="fs-5 text-success fw-bolder">
                                {{ transform_numeric_value($payment->amount) }}
                            </td>

                            <td class="fw-bolder">
                                {{ $payment->discount ? transform_numeric_value($payment->discount) : '-' }}
                            </td>

                            <td class="fs-5 fw-bolder text-primary">
                                {{ transform_numeric_value($remaining) }}
                            </td>

                            <td>
                                {{ transform_numbers($payment->date->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
                            </td>

                            <td>
                                <a href="{{ route('customer-payments.byDate', [
                                    'date' => $payment->date->format('Y-m-d'),
                                ]) }}"
                                    class="btn btn-secondary btn-sm">
                                    الذهاب إلى تاريخ التحصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">
                                لا توجد عمليات تحصيل لهذا الزبون.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $payments->links() }}
        </div>

    </div>
@endsection
