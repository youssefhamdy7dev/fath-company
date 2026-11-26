@props(['payment', 'customers'])

<x-modal-form id="editStrawberryCustomerPaymentModal{{ $payment->id }}" title="تعديل تحصيل الفراولة">

    <form id="editStrawberryCustomerPaymentForm{{ $payment->id }}" method="POST"
        action="{{ route('strawberry-customer-payments.update', $payment->id) }}">

        @csrf

        {{-- Customer --}}
        <div class="mb-3">
            <label for="edit_strawberry_customer_id{{ $payment->id }}" class="form-label">
                الزبون
            </label>

            <select name="customer_id" id="edit_strawberry_customer_id{{ $payment->id }}" class="form-select">
                <option value="" {{ $payment->customer_id === null ? 'selected' : '' }}>
                    نقدية
                </option>

                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $payment->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ( {{ $customer->location }} )
                    </option>
                @endforeach
            </select>

            <div class="invalid-feedback"></div>
        </div>

        {{-- Amount --}}
        <div class="mb-3">
            <label for="edit_strawberry_amount{{ $payment->id }}" class="form-label">
                المبلغ
            </label>

            <input type="number" name="amount" id="edit_strawberry_amount{{ $payment->id }}" class="form-control"
                value="{{ $payment->amount }}">

            <div class="invalid-feedback"></div>
        </div>

        {{-- Discount --}}
        <div class="mb-3">
            <label for="edit_strawberry_discount{{ $payment->id }}" class="form-label">
                الخصم
            </label>

            <input type="number" step="0.01" name="discount" id="edit_strawberry_discount{{ $payment->id }}"
                class="form-control" value="{{ $payment->discount }}">

            <div class="invalid-feedback"></div>
        </div>

        {{-- Date --}}
        <div class="mb-3">
            <label for="edit_strawberry_date{{ $payment->id }}" class="form-label">
                التاريخ
            </label>

            <input type="text" name="date" id="edit_strawberry_date{{ $payment->id }}"
                class="form-control globdatepicker" value="{{ $payment->date->format('Y-m-d') }}" autocomplete="off">

            <div class="invalid-feedback"></div>
        </div>

        {{-- Submit --}}
        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-warning px-4">
                تعديل <i class="ps-1 bi bi-pencil-square"></i>
            </button>
        </div>

    </form>

    <script>
        document.getElementById('editStrawberryCustomerPaymentForm{{ $payment->id }}')
            .addEventListener('submit', function(event) {
                event.preventDefault();

                const form = this;
                const url = form.getAttribute('action');
                const formData = new FormData(form);

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                // Clear previous errors
                form.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });

                form.querySelectorAll('.invalid-feedback').forEach(element => {
                    element.textContent = '';
                    element.style.display = '';
                });

                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = 'جاري التعديل... <i class="ps-1 bi bi-hourglass-split"></i>';

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
