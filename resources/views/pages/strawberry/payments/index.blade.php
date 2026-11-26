@extends('layouts.app')

<title>شركة الفتح | تحصيل الفراولة</title>

@section('content')
    <div class="card shadow-sm p-4 text-center">

        {{-- Top actions --}}
        <div class="d-flex justify-content-end mb-3">
            <div class="d-flex gap-2">
                <a href="{{ route('strawberry-customer-payments.daily.create') }}" class="btn btn-success">
                    تسجيل يومية تحصيل <i class="ps-1 bi bi-journal-plus"></i>
                </a>

                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                    data-bs-target="#addStrawberryCustomerPaymentModal">
                    إضافة تحصيل زبون <i class="ps-1 bi bi-plus-circle-fill"></i>
                </button>
            </div>
        </div>

        {{-- Payments ledger --}}
        <h3 class="fw-bold mt-3 mb-3">دفتر تحصيل <span class="badge bg-danger">الفراولة</span></h3>

        <div class="table-responsive w-100">
            <table class="table table-secondary table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>تفاصيل</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($dates as $date)
                        <tr>
                            <td>{{ transform_numeric_value($loop->iteration) }}</td>

                            <td>
                                {{ transform_numbers(\Carbon\Carbon::parse($date)->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
                            </td>

                            <td>
                                <a href="{{ route('strawberry-customer-payments.byDate', ['date' => $date->format('Y-m-d')]) }}"
                                    class="btn btn-info btn-sm">
                                    عرض يومية التحصيل <i class="ps-1 bi bi-eye-fill"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-muted">
                                لا توجد تواريخ تحصيل مسجلة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $dates->links() }}
        </div>
    </div>

    {{-- Add payment modal --}}
    @include('pages.strawberry.payments.partials.add-payment-modal')
@endsection
