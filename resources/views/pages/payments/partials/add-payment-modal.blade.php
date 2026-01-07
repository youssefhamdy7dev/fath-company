<x-modal-form id="addCustomerPaymentModal" title="إضافة تحصيل زبون">

    <form id="addCustomerPaymentForm" method="POST" action="{{ route('customer-payments.store') }}">
        @csrf

        <div class="mb-3">
            <label for="customer_id" class="form-label">الزبون</label>
            <select name="customer_id" id="customer_id" class="form-select">
                <option disabled selected>إختيار الزبون...</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ( {{ $customer->location }} )</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <dv class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input name="amount" type="number" min="1" class="form-control">
            <div class="invalid-feedback"></div>
        </dv>

        <div class="mb-3">
            <label for="discount" class="form-label">الخصم</label>
            <input name="discount" type="number" step="0.01" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="text" name="date" id="date" class="form-control globdatepicker"
                value="{{ $pageDate ?? \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-primary px-4">
                إضافة<i class="ps-1 bi bi-plus-circle"></i>
            </button>
        </div>
    </form>

    <script>
        document.getElementById('addCustomerPaymentForm').addEventListener('submit', function(e) {
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
