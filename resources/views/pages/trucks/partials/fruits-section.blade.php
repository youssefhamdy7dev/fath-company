{{-- Fruits blocks heading and add button --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="m-0">إضافة أصناف للعربة</h5>
    <button type="button" id="addFruitBtn" class="btn btn-outline-success btn-sm">
        إضافة صنف آخر
        <i class="bi bi-plus-lg"></i>
    </button>
</div>

{{-- Fruits container --}}
<div id="fruitsContainer">
    @php
        $oldFruits = old('fruits', []);
    @endphp

    @if (count($oldFruits) > 0)
        @foreach ($oldFruits as $idx => $fruitData)
            @include('pages.trucks.partials.fruit-block', ['index' => $idx, 'fruitData' => $fruitData])
        @endforeach
    @else
        {{-- Default single block for new truck --}}
        @include('pages.trucks.partials.fruit-block', ['index' => 0, 'fruitData' => []])
    @endif
</div>
