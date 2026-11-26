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

        <h3 class="mb-3">تعديل عربة فراولة</h3>

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger w-75" dir="rtl">
                <strong>حدثت أخطاء أثناء حفظ التعديلات:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('strawberry-trucks.update', $truck) }}" class="form-control" dir="rtl"
            id="strawberryTruckEditForm">

            @csrf
            @method('PUT')

            <div class="container w-75">

                {{-- Truck information --}}
                <div class="d-flex gap-3">
                    <x-form.input-group name="date" label="التاريخ" type="text"
                        value="{{ old('date', $truck->date->format('Y-m-d')) }}" class="globdatepicker"
                        autocomplete="off" />

                    <x-form.input-group name="total_boxes" label="إجمالي عدد الصناديق" type="number" min="1"
                        value="{{ old('total_boxes', $truck->total_boxes) }}" />
                </div>

                <div class="d-flex gap-3">
                    <x-form.select-group name="driver_id" label="السائق" :options="$drivers->pluck('name', 'id')"
                        selected="{{ old('driver_id', $truck->driver_id) }}" />

                    <x-form.input-group name="freight" label="النولون" type="number" min="0"
                        value="{{ old('freight', $truck->freight) }}" />
                </div>

                <hr class="my-4">

                {{-- Fruits section --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">أصناف العربة</h5>

                    <button type="button" id="addFruitBtn" class="btn btn-outline-success btn-sm">
                        إضافة صنف آخر
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div id="fruitsContainer">

                    @php
                        $oldFruits = old(
                            'fruits',
                            $truck->truckFruits
                                ->map(function ($fruit) {
                                    return [
                                        'id' => $fruit->id,
                                        'fruit_type' => $fruit->fruit_type,
                                        'client_id' => $fruit->client_id,
                                        'box_type' => $fruit->box_type,
                                        'number_of_boxes' => $fruit->number_of_boxes,
                                        'second_class_boxes' => $fruit->second_class_boxes,
                                        'third_class_boxes' => $fruit->third_class_boxes,
                                        'unified_weight' => $fruit->unified_weight,
                                        'unified_unit_price' => $fruit->unified_unit_price,
                                        'unified_box_price' => $fruit->unified_box_price,
                                    ];
                                })
                                ->toArray(),
                        );
                    @endphp

                    @if (count($oldFruits) > 0)
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

                {{-- Submit --}}
                <div class="text-center">
                    <button type="submit" class="btn btn-reload btn-primary mt-4 px-5">
                        حفظ التعديلات
                        <i class="bi bi-save"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Template for dynamically added fruits --}}
    @include('pages.strawberry.trucks.partials.fruit-block-template')

    {{-- Fruits JavaScript --}}
    @include('pages.strawberry.trucks.partials.fruit-blocks-script')
@endsection
