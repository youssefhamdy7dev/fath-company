@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center">
        {{-- Alerts --}}
        <x-alerts.session-status />

        {{-- Back to index --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('trucks.index') }}" class="btn btn-info">
                العودة إلى قائمة العربات <i class="bi bi-truck"></i>
            </a>
        </div>

        <h3 class="mb-3">إضافة عربة جديدة</h3>

        <form method="POST" action="{{ route('trucks.store') }}" class="form-control" dir="rtl" id="truckCreateForm">
            @csrf

            <div class="container w-75">
                {{-- Truck Meta Section --}}
                @include('pages.trucks.partials.truck-meta-fields')

                <hr class="my-4">

                {{-- Fruits Section --}}
                @include('pages.trucks.partials.fruits-section')

                {{-- Submit --}}
                <div class="text-center">
                    <button type="submit" class="btn btn-reload btn-primary mt-4 px-5">
                        إضافة العربة<i class="ps-1 bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Template for JS clone --}}
    @include('pages.trucks.partials.fruit-block-template')

    {{-- Fruits blocks behavior --}}
    @include('pages.trucks.partials.fruit-blocks-script')
@endsection
