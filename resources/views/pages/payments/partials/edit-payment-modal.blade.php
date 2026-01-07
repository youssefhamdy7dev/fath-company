@props(['payment', 'customers'])

<x-modal-form id="editCustomerPaymentModal{{ $payment->id }}" title="تعديل تحصيل الزبون">

    <form id="editCustomerPaymentForm{{ $payment->id }}" method="POST"
        action="{{ route('customer-payments.update', $payment->id) }}">
        @csrf
        @method('POST') {{-- since your route uses POST for update --}}

        <div class="mb-3">
            <label class="form-label">الزبون</label>
            <select name="customer_id" class="form-select" id="edit_customer_id">
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $payment->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} - ( {{ $customer->location }} )
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label class="form-label">المبلغ</label>
            <input type="number" name="amount" class="form-control" id="edit_amount" min="1"
                value="{{ $payment->amount }}">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label class="form-label">الخصم</label>
            <input type="number" step="0.01" name="discount" class="form-control" id="edit_discount"
                value="{{ $payment->discount }}">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="text" name="date" id="date" class="form-control globdatepicker"
                value="{{ $payment->date }}"autocomplete="off">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-warning px-4">
                تعديل <i class="ps-1 bi bi-pencil-square"></i>
            </button>
        </div>
    </form>

    {{-- Ajax --}}
    <script>
        document.getElementById('editCustomerPaymentForm{{ $payment->id }}')
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
                            window.location.href = res.redirect_url || window.location.href;
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
