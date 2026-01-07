@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4">

        <div class="d-flex justify-content-end gap-2 w-100 mb-3">
            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-dark">
                العودة لبيانات الزبون <i class="bi bi-arrow-up-left"></i>
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-info">
                العودة إلى قائمة الزبائن <i class="bi bi-person-lines-fill"></i>
            </a>
        </div>

        <h2 class="text-center text-muted">سجل مشتروات وتحصيلات الزبون: <span
                class="text-primary-emphasis">{{ $customer->name }}</span></h2>

        <div>
            @include('pages.customers.partials.purchases-grouped')
        </div>
    </div>

    <x-scroll-button />
@endsection
