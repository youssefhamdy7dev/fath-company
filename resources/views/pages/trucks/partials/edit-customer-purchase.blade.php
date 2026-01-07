@props(['purchase', 'truck', 'customers', 'boxTypes'])

<x-modal-form id="editCustomerPurchaseModal{{ $purchase->id }}" title="تعديل مشتروات زبون">

    <form id="editCustomerPurchaseForm{{ $purchase->id }}" method="POST"
        action="{{ route('customer-purchases.update', $purchase->id) }}">
        @csrf
        @method('POST') {{-- since your route uses POST for update --}}

        <div class="row g-3">

            {{-- Customer --}}
            <div class="col-md-6">
                <div class="form-div">
                    <label class="form-label">الزبون</label>
                    <select name="customer_id" class="form-select">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ $purchase->customer_id == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Truck Fruit --}}
            <div class="col-md-6">
                <div class="form-div">
                    <label class="form-label">الصنف</label>
                    <select name="truck_fruit_id" class="form-select">
                        @foreach ($truck->truckFruits as $tf)
                            <option value="{{ $tf->id }}"
                                {{ $purchase->truck_fruit_id == $tf->id ? 'selected' : '' }}>
                                {{ $tf->fruit->name }} -
                                {{ $boxTypes[$tf->box_type] ?? $tf->box_type }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Box class --}}
            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">فئة المشتروات</label>
                    <select name="box_class" class="form-select">
                        <option value="first" {{ $purchase->box_class == 'first' ? 'selected' : '' }}>فاخر</option>
                        <option value="second" {{ $purchase->box_class == 'second' ? 'selected' : '' }}>نمرة 2</option>
                        <option value="third" {{ $purchase->box_class == 'third' ? 'selected' : '' }}>نمرة 3 / هالك
                        </option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Boxes --}}
            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">عدد البرانيك</label>
                    <input type="number" name="number_of_boxes" class="form-control"
                        value="{{ $purchase->number_of_boxes }}">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Weight --}}
            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">إجمالي الوزن الصافي</label>
                    <input type="number" step="0.01" name="total_weight" class="form-control"
                        value="{{ $purchase->total_weight }}">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Unit price --}}
            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">الفئة بالكيلو (خاص)</label>
                    <input type="number" step="0.01" name="unique_unit_price" class="form-control"
                        value="{{ $purchase->unique_unit_price }}">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Box price --}}
            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">الفئة بالبرنيكة (خاص)</label>
                    <input type="number" name="unique_box_price" class="form-control"
                        value="{{ $purchase->unique_box_price }}">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Date --}}
            <div class="col-md-4">
                <div class="form-div">
                    <label for="date" class="form-label">التاريخ</label>
                    <input type="text" name="date" id="date" class="form-control purchasedate"
                        value="{{ $purchase->date->format('Y-m-d') }}"
                        data-min-date="{{ $truck->date->format('Y-m-d') }}" autocomplete="off">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

        </div>

        {{-- Submit --}}
        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-warning px-4">
                تعديل <i class="ps-1 bi bi-pencil-square"></i>
            </button>
        </div>
    </form>


    {{-- Ajax --}}
    <script>
        document.getElementById('editCustomerPurchaseForm{{ $purchase->id }}')
            .addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const url = form.getAttribute('action');
                const formData = new FormData(form);

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = 'جاري التعديل... <i class="ps-1 bi bi-hourglass-split"></i>';

                // clear errors
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.errors) {
                            for (const [field, msgs] of Object.entries(res.errors)) {
                                let input = form.querySelector(`[name="${field}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    input.parentElement.querySelector('.invalid-feedback').textContent = msgs[0];
                                }
                            }
                            submitBtn.classList.remove('disabled');
                            submitBtn.innerHTML = originalBtnText;
                        } else {
                            window.location.href = res.redirect_url;
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        submitBtn.classList.remove('disabled');
                        submitBtn.innerHTML = originalBtnText;
                        alert('حدث خطأ أثناء الإرسال');
                    });
            });
    </script>

</x-modal-form>
