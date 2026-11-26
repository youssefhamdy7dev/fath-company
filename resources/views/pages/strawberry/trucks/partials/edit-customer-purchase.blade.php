<x-modal-form id="editStrawberryPurchaseModal{{ $purchase->id }}" title="تعديل مشتروات الفراولة">

    <form id="editStrawberryPurchaseForm{{ $purchase->id }}" method="POST"
        action="{{ route('strawberry-customer-purchases.update', $purchase->id) }}">

        @csrf

        <div class="row g-3">

            <div class="col-md-6">
                <div class="form-div">
                    <label class="form-label">الزبون</label>

                    <select name="customer_id" class="form-select">
                        <option value="" @selected(!$purchase->customer_id)>نقدية</option>

                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected($purchase->customer_id == $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-div">
                    <label class="form-label">الصنف</label>

                    <select name="strawberry_truck_fruit_id" class="form-select">
                        @foreach ($truck->truckFruits as $fruit)
                            <option value="{{ $fruit->id }}" @selected($purchase->strawberry_truck_fruit_id == $fruit->id)>
                                {{ $fruit->fruit_type }}
                                - {{ $boxTypes[$fruit->box_type] ?? $fruit->box_type }}
                            </option>
                        @endforeach
                    </select>

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">فئة المشتروات</label>

                    <select name="box_class" class="form-select">
                        <option value="first" @selected($purchase->box_class === 'first')>فاخر</option>
                        <option value="second" @selected($purchase->box_class === 'second')>نمرة 2</option>
                        <option value="third" @selected($purchase->box_class === 'third')>نمرة 3 / هالك</option>
                    </select>

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">عدد الصناديق</label>

                    <input type="number" name="number_of_boxes" min="1" class="form-control"
                        value="{{ $purchase->number_of_boxes }}">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">إجمالي الوزن الفعلي</label>

                    <input type="number" name="total_weight" min="0" step="0.01" class="form-control"
                        value="{{ $purchase->getRawOriginal('total_weight') }}">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">سعر الكيلو الخاص</label>

                    <input type="number" name="unique_unit_price" min="1" step="0.01" class="form-control"
                        value="{{ $purchase->unique_unit_price }}">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">سعر البرنيكة الخاص</label>

                    <input type="number" name="unique_box_price" min="1" class="form-control"
                        value="{{ $purchase->unique_box_price }}">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">التاريخ</label>

                    <input type="text" name="date" class="form-control purchasedate"
                        value="{{ $purchase->date->format('Y-m-d') }}"
                        data-min-date="{{ $truck->date->format('Y-m-d') }}" autocomplete="off">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

        </div>

        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-warning px-4">
                تعديل <i class="ps-1 bi bi-pencil-square"></i>
            </button>
        </div>
    </form>

    <script>
        document.getElementById('editStrawberryPurchaseForm{{ $purchase->id }}')
            .addEventListener('submit', function(event) {
                event.preventDefault();

                const form = this;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                form.querySelectorAll('.is-invalid').forEach(input => {
                    input.classList.remove('is-invalid');
                });

                form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                    feedback.textContent = '';
                });

                submitBtn.disabled = true;
                submitBtn.innerHTML = 'جاري التعديل... <i class="bi bi-hourglass-split"></i>';

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    })
                    .then(response => response.json())
                    .then(response => {
                        if (response.errors) {
                            for (const [field, messages] of Object.entries(response.errors)) {
                                const input = form.querySelector(`[name="${field}"]`);

                                if (!input) continue;

                                input.classList.add('is-invalid');

                                const feedback = input.parentElement.querySelector('.invalid-feedback');

                                if (feedback) feedback.textContent = messages[0];
                            }

                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            return;
                        }

                        window.location.href = response.redirect_url || window.location.href;
                    })
                    .catch(error => {
                        console.error(error);

                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;

                        alert('حدث خطأ أثناء الإرسال');
                    });
            });
    </script>

</x-modal-form>
