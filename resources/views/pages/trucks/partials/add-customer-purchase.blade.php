@props(['truck', 'customers'])

<x-modal-form id="addCustomerPurchaseModal" title="إضافة مشتروات زبون">

    <form id="addCustomerPurchaseForm" method="POST" action="{{ route('customer-purchases.store') }}">
        @csrf

        <div class="row g-3">

            {{-- Row 1 --}}
            <div class="col-md-6">
                {{-- الزبون --}}
                <div class="form-div">
                    <label for="customer_id" class="form-label">الزبون</label>
                    <select name="customer_id" id="customer_id" class="form-select">
                        <option disabled selected>إختيار الزبون...</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} - ( {{ $customer->location }} )
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-6">
                {{-- الصنف (truck_fruit) --}}
                <div class="form-div">
                    <label for="truck_fruit_id" class="form-label">الصنف</label>
                    <select name="truck_fruit_id" id="truck_fruit_id" class="form-select">
                        @if ($truck->truckFruits->count() > 1)
                            <option disabled selected>إختيار الصنف...</option>
                        @endif
                        @foreach ($truck->truckFruits as $truckFruit)
                            <option value="{{ $truckFruit->id }}">
                                {{ $truckFruit->fruit->name }}
                                -
                                {{ $boxTypes[$truckFruit->box_type] ?? $truckFruit->box_type }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Row 2 --}}

            <div class="col-md-4">
                {{-- فئة --}}
                <div class="form-div">
                    <label for="box_class" class="form-label">فئة المشتروات</label>
                    <select name="box_class" id="box_class" class="form-select">
                        @if ($truck->truckFruits->some(fn($truckFruit) => $truckFruit->second_class_boxes == null && $truckFruit->third_class_boxes == null) || $truck->numberOfLowClassBoughtBoxes >= $truck->numberOfLowClassBoxes)
                            <option value="first" selected>فاخر</option>
                        @else
                            <option disabled selected>إختيار الفئة...</option>
                            <option value="first">فاخر</option>
                            <option value="second">نمرة 2</option>
                            <option value="third">نمرة 3 / هالك</option>
                        @endif
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- عدد البرانيك --}}
                <div class="form-div">
                    <label for="number_of_boxes" class="form-label">عدد البرانيك</label>
                    <input type="number" name="number_of_boxes" id="number_of_boxes" class="form-control"
                        min="1">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- إجمالي الوزن الصافي --}}
                <div class="form-div">
                    <label for="total_weight" class="form-label">إجمالي الوزن الصافي</label>
                    <input type="number" name="total_weight" id="total_weight" class="form-control" step="0.01">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Row 3 --}}
            <div class="col-md-4">
                {{-- الفئة بالكيلو (خاص) --}}
                <div class="form-div">
                    <label for="unique_unit_price" class="form-label">الفئة بالكيلو (خاص)</label>
                    <input type="number" name="unique_unit_price" id="unique_unit_price" class="form-control"
                        step="0.01">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- الفئة بالبرنيكة (خاص) --}}
                <div class="form-div">
                    <label for="unique_box_price" class="form-label">الفئة بالبرنيكة (خاص)</label>
                    <input type="number" name="unique_box_price" id="unique_box_price" class="form-control">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- تاريخ الشراء --}}
                <div class="form-div">
                    <label for="date" class="form-label">التاريخ</label>
                    <input type="text" name="date" id="date" class="form-control purchasedate"
                        value="{{ $truck->date->isToday() ? $truck->date->format('Y-m-d') : old('date') }}"
                        data-min-date="{{ $truck->date->format('Y-m-d') }}" autocomplete="off">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-primary px-4">
                إضافة<i class="ps-1 bi bi-plus-circle"></i>
            </button>
        </div>
    </form>

    <script>
        document.getElementById('addCustomerPurchaseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const url = form.getAttribute('action');
            const formData = new FormData(form);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.classList.add('disabled');
            submitBtn.innerHTML = 'جاري الإضافة... <i class="ps-1 bi bi-hourglass-split"></i>';

            // Clear previous errors
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.errors) {
                        // Handle validation errors
                        for (const [field, messages] of Object.entries(res.errors)) {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.parentElement.querySelector('.invalid-feedback');
                                if (feedback) {
                                    feedback.textContent = messages[0];
                                }
                            }
                        }
                        submitBtn.classList.remove('disabled');
                        submitBtn.innerHTML = originalBtnText;
                    } else {
                        // Handle success
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
