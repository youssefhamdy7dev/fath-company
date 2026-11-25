@props(['payment', 'customers'])

<x-modal-form id="editCustomerPaymentModal{{ $payment->id }}" title="تعديل تحصيل الزبون">
    <form id="editCustomerPaymentForm{{ $payment->id }}" method="POST"
        action="{{ route('customer-payments.update', $payment->id) }}">
        @csrf
        @method('POST') {{-- since your route uses POST for update --}}

        {{-- Customer --}}
        <div class="mb-3">
            <label for="edit_customer_id{{ $payment->id }}" class="form-label">
                الزبون
            </label>

            <select name="customer_id" id="edit_customer_id{{ $payment->id }}" class="form-select">
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $payment->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} - ( {{ $customer->location }} )
                    </option>
                @endforeach
            </select>

            <div class="invalid-feedback"></div>
        </div>

        {{-- Amount --}}
        <div class="mb-3">
            <label for="edit_amount{{ $payment->id }}" class="form-label">
                المبلغ
            </label>

            <input type="number" name="amount" id="edit_amount{{ $payment->id }}" class="form-control"
                value="{{ $payment->amount }}">

            <div class="invalid-feedback"></div>
        </div>

        {{-- Discount --}}
        <div class="mb-3">
            <label for="edit_discount{{ $payment->id }}" class="form-label">
                الخصم
            </label>

            <input type="number" step="0.01" name="discount" id="edit_discount{{ $payment->id }}"
                class="form-control" value="{{ $payment->discount }}">

            <div class="invalid-feedback"></div>
        </div>

        {{-- Date --}}
        <div class="mb-3">
            <label for="edit_date{{ $payment->id }}" class="form-label">
                التاريخ
            </label>

            <input type="text" name="date" id="edit_date{{ $payment->id }}" class="form-control globdatepicker"
                value="{{ $payment->date }}" autocomplete="off">

            <div class="invalid-feedback"></div>
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
        document.getElementById('editCustomerPaymentForm{{ $payment->id }}')
            .addEventListener('submit', function(event) {
                event.preventDefault();

                const form = this;
                const url = form.getAttribute('action');
                const formData = new FormData(form);

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = 'جاري التعديل... <i class="ps-1 bi bi-hourglass-split"></i>';

                // Clear previous errors
                form.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });

                form.querySelectorAll('.invalid-feedback').forEach(element => {
                    element.textContent = '';
                });

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(response => {
                        if (response.errors) {
                            for (const [field, messages] of Object.entries(response.errors)) {
                                const input = form.querySelector(`[name="${field}"]`);

                                if (!input) {
                                    continue;
                                }

                                input.classList.add('is-invalid');

                                const feedback = input.parentElement.querySelector('.invalid-feedback');

                                if (feedback) {
                                    feedback.textContent = messages[0];
                                }
                            }

                            submitBtn.classList.remove('disabled');
                            submitBtn.innerHTML = originalBtnText;
                            return;
                        }

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
