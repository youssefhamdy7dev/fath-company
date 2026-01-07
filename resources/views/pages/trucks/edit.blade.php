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

        <h3 class="mb-3">تعديل عربة</h3>

        <form method="POST" action="{{ route('trucks.update', $truck) }}" class="form-control" dir="rtl"
            id="truckEditForm">
            @csrf
            @method('PUT')

            <div class="container w-75">
                {{-- Truck Meta Section --}}
                <div class="d-flex gap-3">
                    <x-form.input-group name="date" label="التاريخ" type="text"
                        value="{{ old('date', $truck->date) }}" class="datepicker" autocomplete="off" />

                    <x-form.input-group name="total_boxes" label="إجمالى عدد البرانيك" type="number" min="0"
                        value="{{ old('total_boxes', $truck->total_boxes) }}" />
                </div>

                <div class="d-flex gap-3">
                    <x-form.select-group name="driver_id" label="السائق" :options="$drivers->pluck('name', 'id')"
                        selected="{{ old('driver_id', $truck->driver_id) }}" />

                    <x-form.input-group name="freight" label="النولون" type="number" min="0" step="0.01"
                        value="{{ old('freight', $truck->freight) }}" />
                </div>

                <hr class="my-4">

                {{-- Fruits Section --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">الأصناف في العربة</h5>
                    <button type="button" id="addFruitBtn" class="btn btn-outline-success btn-sm">
                        إضافة صنف آخر
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                {{-- Fruits container --}}
                <div id="fruitsContainer">
                    @php
                        $oldFruits = old(
                            'fruits',
                            $truck->truckFruits
                                ->map(function ($fruit) {
                                    return [
                                        'fruit_id' => $fruit->fruit_id,
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
                            @include('pages.trucks.partials.fruit-block', [
                                'index' => $idx,
                                'fruitData' => $fruitData,
                            ])
                        @endforeach
                    @else
                        @include('pages.trucks.partials.fruit-block', ['index' => 0, 'fruitData' => []])
                    @endif
                </div>

                {{-- Submit --}}
                <div class="text-center">
                    <button type="submit" class="btn btn-reload btn-primary mt-4 px-5">
                        حفظ التعديلات <i class="bi bi-save"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Template for JS clone --}}
    @include('pages.trucks.partials.fruit-block-template')

    <script>
        // Same JavaScript as create.blade.php
        document.addEventListener('DOMContentLoaded', function() {
            const fruitsContainer = document.getElementById('fruitsContainer');
            const addFruitBtn = document.getElementById('addFruitBtn');
            const template = document.getElementById('fruitBlockTemplate').innerHTML.trim();

            function getCurrentMaxIndex() {
                const blocks = fruitsContainer.querySelectorAll('.fruit-block');
                let max = -1;
                blocks.forEach(block => {
                    const idx = parseInt(block.dataset.index);
                    if (!isNaN(idx) && idx > max) max = idx;
                });
                return max;
            }

            function addFruitBlock() {
                const nextIndex = getCurrentMaxIndex() + 1;
                let html = template.replace(/__INDEX__/g, nextIndex).replace(/__LABEL__/g, nextIndex + 1);

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const block = tempDiv.firstElementChild;

                fruitsContainer.appendChild(block);
            }

            function ensureAtLeastOne() {
                if (fruitsContainer.querySelectorAll('.fruit-block').length === 0) {
                    addFruitBlock();
                }
            }

            function reindexFruitBlocks() {
                fruitsContainer.querySelectorAll('.fruit-block').forEach((b, i) => {
                    b.dataset.index = i;
                    b.querySelector('strong').textContent = 'الصنف رقم ' + (i + 1);

                    b.querySelectorAll('select, input').forEach(input => {
                        const name = input.name.replace(/\[\d+\]/, `[${i}]`);
                        input.name = name;
                    });
                });
            }

            addFruitBtn.addEventListener('click', addFruitBlock);

            fruitsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-fruit-btn')) {
                    const block = e.target.closest('.fruit-block');
                    block.remove();
                    ensureAtLeastOne();
                    reindexFruitBlocks();
                }
            });

            ensureAtLeastOne();
        });
    </script>
@endsection
