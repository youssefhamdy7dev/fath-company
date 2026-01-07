@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-end w-100 mb-3">
        <a href="{{ route('customers.index') }}" class="btn btn-info">
            الذهاب إلى قائمة الزبائن <i class="bi bi-person-lines-fill"></i>
        </a>
    </div>

    <form method="GET" action="{{ route('customers.reports') }}" id="reportsForm" class="form-control" autocomplete="off">
        @csrf
        <div class="mb-3 d-flex justify-content-center text-center">
            <div class="col-md-3">
                <label for="date" class="form-label fs-5 fw-bolder text-primary">تاريخ كشف المشتروات</label>
                <div class="d-flex gap-2">
                    <input type="text" id="date"
                        class="form-control globdatepicker @error('date') is-invalid @enderror" name="date"
                        value="{{ $date }}">
                    @error('date')
                        <div class="invalid-feedback fw-bolder">{{ $message }}</div>
                    @enderror
                    <button class="btn btn-primary d-flex" type="submit">
                        بحث<i class="ps-1 bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- DATE HEADER --}}
    <h2 class="text-center fw-bold mb-4 mt-3">
        كشف مشتروات الزبائن بتاريخ:
        <span
            class="text-primary">{{ transform_numbers(\Carbon\Carbon::parse($date)->locale('ar')->translatedFormat('l ( d-m-Y )')) }}
        </span>
    </h2>

    {{-- NO DATA --}}
    @if (count($reportData) === 0)
        <div class="alert alert-warning text-center fs-4">
            لا توجد مشتروات لهذا التاريخ.
        </div>
    @endif

    {{-- LIST OF CUSTOMERS --}}
    <div class="row">
        @foreach ($reportData as $entry)
            {{-- <div class="col-12 col-md-6 col-lg-4"> --}}
            <div class="col-12 col-md-6">
                @include('pages.customers.partials.daily-report-customer', [
                    'data' => $entry,
                    'date' => $date,
                ])
            </div>
        @endforeach
    </div>

    <x-scroll-button />
@endsection
