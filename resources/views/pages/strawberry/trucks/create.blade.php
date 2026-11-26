@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center">

        <x-alerts.session-status />

        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('strawberry-trucks.index') }}" class="btn btn-info">
                العودة إلى قائمة عربات الفراولة
                <i class="bi bi-truck"></i>
            </a>
        </div>

        <h3 class="mb-3">إضافة عربة فراولة جديدة</h3>

        <form method="POST" action="{{ route('strawberry-trucks.store') }}" class="form-control" dir="rtl"
            id="strawberryTruckCreateForm">

            @csrf

            <div class="container w-75">

                @include('pages.strawberry.trucks.partials.truck-meta-fields')

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">أصناف العربة</h5>

                    <button type="button" id="addFruitBtn" class="btn btn-outline-success btn-sm">
                        إضافة صنف آخر <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div id="fruitsContainer">
                    @php
                        $oldFruits = old('fruits', []);
                    @endphp

                    @if (count($oldFruits))
                        @foreach ($oldFruits as $idx => $fruitData)
                            @include('pages.strawberry.trucks.partials.fruit-block', [
                                'index' => $idx,
                                'fruitData' => $fruitData,
                            ])
                        @endforeach
                    @else
                        @include('pages.strawberry.trucks.partials.fruit-block', [
                            'index' => 0,
                            'fruitData' => [],
                        ])
                    @endif
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-reload btn-primary mt-4 px-5">
                        إضافة العربة <i class="ps-1 bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @include('pages.strawberry.trucks.partials.fruit-block-template')
    @include('pages.strawberry.trucks.partials.fruit-blocks-script')
@endsection
