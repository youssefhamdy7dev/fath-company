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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fruitsContainer = document.getElementById('fruitsContainer');
            const addFruitBtn = document.getElementById('addFruitBtn');
            const template = document.getElementById('fruitBlockTemplate').innerHTML.trim();

            // Get highest index from existing blocks
            function getCurrentMaxIndex() {
                const blocks = fruitsContainer.querySelectorAll('.fruit-block');
                let max = -1;
                blocks.forEach(block => {
                    const idx = parseInt(block.dataset.index);
                    if (!isNaN(idx) && idx > max) max = idx;
                });
                return max;
            }

            // Add new fruit section
            function addFruitBlock() {
                const nextIndex = getCurrentMaxIndex() + 1;
                let html = template.replace(/__INDEX__/g, nextIndex).replace(/__LABEL__/g, nextIndex + 1);

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const block = tempDiv.firstElementChild;

                fruitsContainer.appendChild(block);
            }

            // Ensure at least one block exists
            function ensureAtLeastOne() {
                if (fruitsContainer.querySelectorAll('.fruit-block').length === 0) {
                    addFruitBlock();
                }
            }

            // Add button
            addFruitBtn.addEventListener('click', addFruitBlock);

            // Delete button (event delegation)
            fruitsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-fruit-btn')) {
                    const block = e.target.closest('.fruit-block');
                    block.remove();
                    ensureAtLeastOne();
                    reindexFruitBlocks();
                }
            });

            // Re-index and re-label blocks
            function reindexFruitBlocks() {
                fruitsContainer.querySelectorAll('.fruit-block').forEach((b, i) => {
                    b.dataset.index = i;
                    b.querySelector('strong').textContent = 'الصنف رقم ' + (i + 1);

                    // Update all input/select names
                    b.querySelectorAll('select, input').forEach(input => {
                        const name = input.name.replace(/\[\d+\]/, `[${i}]`);
                        input.name = name;
                    });
                });
            }

            ensureAtLeastOne();
        });
    </script>
@endsection
