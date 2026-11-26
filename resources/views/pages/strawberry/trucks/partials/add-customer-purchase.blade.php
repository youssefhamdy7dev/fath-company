<x-modal-form id="addCustomerPurchaseModal" title="إضافة مشتروات فراولة">

    <form id="addStrawberryPurchaseForm" method="POST" action="{{ route('strawberry-customer-purchases.store') }}">

        @csrf

        <div class="row g-3">

            <div class="col-md-6">
                <div class="form-div">
                    <label for="customer_id" class="form-label">الزبون</label>

                    <select name="customer_id" id="customer_id" class="form-select">
                        <option disabled selected>إختيار الزبون...</option>

                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }} - ({{ $customer->location }})
                            </option>
                        @endforeach
                    </select>

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-div">
                    <label for="strawberry_truck_fruit_id" class="form-label">الصنف</label>

                    <select name="strawberry_truck_fruit_id" id="strawberry_truck_fruit_id" class="form-select">
                        @if ($truck->truckFruits->count() > 1)
                            <option disabled selected>إختيار الصنف...</option>
                        @endif

                        @foreach ($truck->truckFruits as $fruit)
                            <option value="{{ $fruit->id }}"
                                {{ $fruit->id === $truck->auto_selectable_truck_fruit_id ? 'selected' : '' }}>
                                {{ $fruit->fruit_type }}
                                -
                                {{ $boxTypes[$fruit->box_type] ?? $fruit->box_type }}

                                @if ($fruit->is_fully_purchased)
                                    (خالص)
                                @endif
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
                        <option value="first">فاخر</option>
                        <option value="second">نمرة 2</option>
                        <option value="third">نمرة 3 / هالك</option>
                    </select>

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">عدد الصناديق</label>

                    <input type="number" name="number_of_boxes" min="1" class="form-control">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">إجمالي الوزن الفعلي</label>

                    <input type="number" name="total_weight" min="0" step="0.01" class="form-control">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">سعر الكيلو الخاص</label>

                    <input type="number" name="unique_unit_price" min="1" step="0.01" class="form-control">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">سعر البرنيكة الخاص</label>

                    <input type="number" name="unique_box_price" min="1" class="form-control">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-div">
                    <label class="form-label">التاريخ</label>

                    <input type="text" name="date" class="form-control purchasedate"
                        value="{{ $truck->date->format('Y-m-d') }}" data-min-date="{{ $truck->date->format('Y-m-d') }}"
                        autocomplete="off">

                    <div class="invalid-feedback"></div>
                </div>
            </div>

        </div>

        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-primary px-4">
                إضافة <i class="ps-1 bi bi-plus-circle"></i>
            </button>
        </div>
    </form>

    <script>
        document.getElementById('addStrawberryPurchaseForm')
            .addEventListener('submit', function(event) {
                event.preventDefault();

                submitStrawberryPurchaseForm(this);
            });

        function submitStrawberryPurchaseForm(form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            form.querySelectorAll('.is-invalid').forEach(input => {
                input.classList.remove('is-invalid');
            });

            form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                feedback.textContent = '';
            });

            submitBtn.disabled = true;
            submitBtn.innerHTML = 'جاري الإضافة... <i class="bi bi-hourglass-split"></i>';

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
        }
    </script>

</x-modal-form>
