<x-modal-form id="addStrawberryCustomerPaymentModal" title="إضافة تحصيل فراولة">

    <form id="addStrawberryCustomerPaymentForm" method="POST" action="{{ route('strawberry-customer-payments.store') }}">
        @csrf

        {{-- Customer --}}
        <div class="mb-3">
            <label for="customer_id" class="form-label">الزبون</label>

            <select name="customer_id" id="customer_id" class="form-select">
                <option disabled selected>إختيار الزبون...</option>

                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">
                        {{ $customer->name }} ( {{ $customer->location }} )
                    </option>
                @endforeach
            </select>

            <div class="invalid-feedback"></div>
        </div>

        {{-- Amount --}}
        <div class="mb-3">
            <label for="strawberry_amount" class="form-label">المبلغ</label>
            <input name="amount" id="strawberry_amount" type="number" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        {{-- Discount --}}
        <div class="mb-3">
            <label for="strawberry_discount" class="form-label">الخصم</label>
            <input name="discount" id="strawberry_discount" type="number" step="0.01" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        {{-- Date --}}
        <div class="mb-3">
            <label for="strawberry_payment_date" class="form-label">التاريخ</label>

            <input type="text" name="date" id="strawberry_payment_date" class="form-control globdatepicker"
                value="{{ isset($pageDate) ? $pageDate->format('Y-m-d') : \App\Support\BusinessDate::toDateString() }}"
                autocomplete="off">

            <div class="invalid-feedback"></div>
        </div>

        {{-- Submit --}}
        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-primary px-4">
                إضافة <i class="ps-1 bi bi-plus-circle"></i>
            </button>
        </div>
    </form>

    <script>
        document.getElementById('addStrawberryCustomerPaymentForm')
            .addEventListener('submit', function(event) {
                event.preventDefault();

                const form = this;
                const url = form.getAttribute('action');
                const formData = new FormData(form);

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                // Clear previous validation errors
                form.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });

                form.querySelectorAll('.invalid-feedback').forEach(element => {
                    element.textContent = '';
                    element.style.display = '';
                });

                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = 'جاري الإضافة... <i class="ps-1 bi bi-hourglass-split"></i>';

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(response => {

                        // Handle validation errors
                        if (response.errors) {
                            for (const [field, messages] of Object.entries(response.errors)) {
                                const input = form.querySelector(`[name="${field}"]`);

                                if (!input) continue;

                                input.classList.add('is-invalid');

                                const formGroup = input.closest('.mb-3');
                                const feedback = formGroup?.querySelector('.invalid-feedback');

                                if (feedback) {
                                    feedback.textContent = messages[0];
                                    feedback.style.display = 'block';
                                }
                            }

                            submitBtn.classList.remove('disabled');
                            submitBtn.innerHTML = originalBtnText;
                            return;
                        }

                        // Handle success
                        window.location.href = response.redirect_url || window.location.href;
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        submitBtn.classList.remove('disabled');
                        submitBtn.innerHTML = originalBtnText;

                        alert('حدث خطأ أثناء الإرسال');
                    });
            });
    </script>

</x-modal-form>
