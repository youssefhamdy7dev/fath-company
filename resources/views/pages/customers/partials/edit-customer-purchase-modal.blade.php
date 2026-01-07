@php
    $truck = $row->truckFruit->truck;
    $boxTypes = [
        'big_box' => 'برنيكة كبيرة',
        'normal_box' => 'برنيكة صغيرة',
        'small_box' => 'برنيكة 10 كيلو',
        'small_net' => 'برنيكة شبك',
    ];
@endphp
<x-modal-form id="editCustomerPurchaseModal{{ $row->id }}" title="تعديل مشتروات زبون">

    <form id="editCustomerPurchaseForm{{ $row->id }}" method="POST"
        action="{{ route('customer-purchases.update', $row->id) }}">
        @csrf

        <div class="row g-3">

            {{-- Customer (READ ONLY) --}}
            <div class="col-md-6">
                <label class="form-label">الزبون</label>
                <input type="text" class="form-control" value="{{ $row->customer->name }}" disabled>
                <input type="hidden" name="customer_id" value="{{ $row->customer_id }}">
            </div>

            {{-- Truck Fruit --}}
            <div class="col-md-6">
                <label class="form-label">الصنف</label>
                <select name="truck_fruit_id" class="form-select">
                    @foreach ($truck->truckFruits as $tf)
                        <option value="{{ $tf->id }}" {{ $row->truck_fruit_id == $tf->id ? 'selected' : '' }}>
                            {{ $tf->fruit->name }} -
                            {{ $boxTypes[$tf->box_type] ?? $tf->box_type }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Box Class --}}
            <div class="col-md-4">
                <label class="form-label">فئة المشتروات</label>
                <select name="box_class" class="form-select">
                    <option value="first" {{ $row->box_class == 'first' ? 'selected' : '' }}>فاخر</option>
                    <option value="second" {{ $row->box_class == 'second' ? 'selected' : '' }}>نمرة 2</option>
                    <option value="third" {{ $row->box_class == 'third' ? 'selected' : '' }}>نمرة 3 / هالك</option>
                </select>
            </div>

            {{-- Boxes --}}
            <div class="col-md-4">
                <label class="form-label">عدد البرانيك</label>
                <input type="number" name="number_of_boxes" class="form-control" value="{{ $row->number_of_boxes }}">
                <div class="invalid-feedback"></div>
            </div>

            {{-- Weight --}}
            <div class="col-md-4">
                <label class="form-label">إجمالي الوزن الصافي</label>
                <input type="number" step="0.01" name="total_weight" class="form-control"
                    value="{{ $row->total_weight }}">
                <div class="invalid-feedback"></div>
            </div>

            {{-- Unit Price --}}
            <div class="col-md-4">
                <label class="form-label">الفئة بالكيلو (خاص)</label>
                <input type="number" step="0.01" name="unique_unit_price" class="form-control"
                    value="{{ $row->unique_unit_price }}">
            </div>

            {{-- Box Price --}}
            <div class="col-md-4">
                <label class="form-label">الفئة بالبرنيكة (خاص)</label>
                <input type="number" name="unique_box_price" class="form-control"
                    value="{{ $row->unique_box_price }}">
            </div>

            {{-- Date --}}
            <div class="col-md-4">
                <label class="form-label">التاريخ</label>
                <input type="text" name="date" class="form-control purchasedate"
                    value="{{ $row->date->format('Y-m-d') }}" data-min-date="{{ $truck->date->format('Y-m-d') }}">
            </div>
        </div>

        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-warning px-4">
                تعديل <i class="ps-1 bi bi-pencil-square"></i>
            </button>
        </div>
    </form>

    <script>
        document.getElementById('editCustomerPurchaseForm{{ $row->id }}')
            .addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const btn = form.querySelector('button[type="submit"]');
                const formData = new FormData(form);

                btn.disabled = true;

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.errors) {
                            Object.entries(res.errors).forEach(([field, msgs]) => {
                                const input = form.querySelector(`[name="${field}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    input.nextElementSibling.textContent = msgs[0];
                                }
                            });
                            btn.disabled = false;
                        } else {
                            window.location.href = window.location.href;
                        }
                    })
                    .catch(() => {
                        btn.disabled = false;
                        alert('Update failed');
                    });
            });
    </script>

</x-modal-form>
