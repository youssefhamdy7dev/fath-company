<x-modal-form id="createBillModal" title="صرف فاتورة">

    {{-- TOP SIDE: Bill Header Form --}}
    <form id="createBillForm" method="POST" action="{{ route('bills.store') }}" autocomplete="off">
        @csrf

        <input type="hidden" name="truck_id" value="{{ $truck->id }}">

        <div class="mb-3">
            <label for="billing_date" class="form-label">تاريخ الصرف</label>
            <input id="billing_date" type="text" name="billing_date" class="form-control globdatepicker"
                value="{{ old('billing_date') }}">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label for="percentage" class="form-label">العمولة (%)</label>
            <input id="percentage" type="number" name="percentage" min="0" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label for="expenses" class="form-label">مصاريف / خوارج</label>
            <input id="expenses" type="number" name="expenses" min="0" class="form-control"
                placeholder="كتابة إجمالى خوارج فقط ، التفاصيل فالملاحظات...">
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <input id="notes" type="text" name="notes" class="form-control"></input>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3 text-center">
            <input type="hidden" id="grand_total" type="number" name="grand_total"
                value="{{ $truck->totals_by_box_class['total_amount'] }}">
            <div class="invalid-feedback fw-bolder fs-5"></div>
        </div>

        {{-- MIDDLE SIDE: Display Calculations --}}
        <h5 class="mt-4 mb-2 text-center">الفاتورة</h5>

        <div class="table-responsive">
            <table class="table table-secondary table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>عدد</th>
                        <th>وزن</th>
                        <th>فئة</th>
                        <th>مبلغ</th>
                    </tr>
                </thead>
                <tbody>

                    @php
                        $grouped = $truck->totals_by_box_class['by_box_class'];
                        $grandTotal = $truck->totals_by_box_class['total_amount'];
                    @endphp

                    @foreach ($grouped as $boxClass => $priceGroups)
                        @foreach ($priceGroups as $price => $totals)
                            <tr class="fw-bold">
                                <td>{{ transform_numeric_value($totals['total_boxes']) }}</td>
                                <td>{{ transform_numeric_value($totals['total_weight']) }}</td>
                                <td>
                                    {{ transform_numbers($totals['unit_price'] ?? $totals['box_price']) }}
                                </td>
                                <td>{{ transform_numeric_value($totals['total_amount']) }}</td>

                                {{-- hidden to send to backend --}}
                                <input type="hidden"
                                    name="items[{{ $loop->parent->index }}][{{ $loop->index }}][box_class]"
                                    value="{{ $boxClass }}">
                                <input type="hidden"
                                    name="items[{{ $loop->parent->index }}][{{ $loop->index }}][price]"
                                    value="{{ $totals['box_price'] ?? $totals['unit_price'] }}">
                                <input type="hidden"
                                    name="items[{{ $loop->parent->index }}][{{ $loop->index }}][total_boxes]"
                                    value="{{ $totals['total_boxes'] }}">
                                <input type="hidden"
                                    name="items[{{ $loop->parent->index }}][{{ $loop->index }}][total_weight]"
                                    value="{{ $totals['total_weight'] }}">
                                <input type="hidden"
                                    name="items[{{ $loop->parent->index }}][{{ $loop->index }}][total_amount]"
                                    value="{{ $totals['total_amount'] }}">
                            </tr>
                        @endforeach
                    @endforeach

                    <tr class="table-light fw-bold fs-5">
                        <td class="text-danger" colspan="3">الإجمالي</td>
                        <td class="text-danger">{{ transform_numeric_value($grandTotal) }}</td>
                    </tr>
                    <tr class="table-light fw-bold fs-5">
                        <td colspan="3">العمولة</td>
                        <td id="percentage_value"></td>
                    </tr>
                    <tr class="table-light fw-bold fs-5">
                        <td colspan="3">النولون</td>
                        <td>{{ transform_numeric_value($truck->freight) }}</td>
                    </tr>
                    <tr class="table-light fw-bold fs-5">
                        <td colspan="3">مصاريف / خوارج</td>
                        <td id="expenses_value"></td>
                    </tr>
                    <tr class="table-light fw-bold fs-5">
                        <td class="text-success" colspan="3">صافى الفاتورة</td>
                        <td class="text-success" id="finalTotal"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Bottom side --}}
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary px-4">
                صرف <i class="bi bi-save ps-1"></i>
            </button>

            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                إلغاء
            </button>
        </div>
    </form>

    {{-- AJAX submit --}}
    <script>
        document.getElementById('createBillForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const url = form.getAttribute('action');
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const original = submitBtn.innerHTML;
            submitBtn.classList.add('disabled');
            submitBtn.innerHTML = 'جارٍ الصرف...';
            form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(f => f.innerHTML = '');
            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(r => {
                    if (r.errors) {
                        for (const [field, messages] of Object.entries(r.errors)) {
                            const el = form.querySelector(`[name="${field}"]`);
                            if (el) {
                                el.classList.add('is-invalid');
                                el.parentElement.querySelector('.invalid-feedback').innerHTML = messages[0];
                            }
                        }
                        submitBtn.classList.remove('disabled');
                        submitBtn.innerHTML = original;
                    } else {
                        window.location.href = r.redirect_url || window.location.href;
                    }
                })
                .catch(() => {
                    alert('خطأ أثناء الحفظ');
                    submitBtn.classList.remove('disabled');
                    submitBtn.innerHTML = original;
                });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const grandTotal = {{ $grandTotal }}; // bill total before calculations
            const freight = {{ $truck->freight }};
            // Inputs
            const percentageInput = document.getElementById('percentage');
            const expensesInput = document.getElementById('expenses');
            // Display TDs
            const percentageValueTd = document.getElementById('percentage_value');
            const expensesValueTd = document.getElementById('expenses_value');
            const finalTotalTd = document.getElementById('finalTotal');
            // Transform numbers to Arabic
            const transformNumbers = (num) => {
                const western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                const eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                return String(num).split('').map(ch => western.includes(ch) ? eastern[western.indexOf(ch)] : ch)
                    .join('');
            };
            const roundToNearest10 = (number) => {
                return Math.round(number / 10) * 10;
            };
            const calculateAndUpdate = () => {
                const percentage = parseFloat(percentageInput.value) || 0;
                const expenses = parseFloat(expensesInput.value) || 0;
                const percentageValue = Math.floor((percentage / 100) * grandTotal);
                const expensesValue = Math.floor(expenses);
                const finalValue = grandTotal - (freight + percentageValue + expensesValue);
                // Update DOM
                percentageValueTd.textContent = transformNumbers(percentageValue);
                expensesValueTd.textContent = transformNumbers(expensesValue);
                finalTotalTd.textContent = transformNumbers(roundToNearest10(finalValue));
            };
            // Initial calculation
            calculateAndUpdate();
            // Update live when user types
            percentageInput.addEventListener('input', calculateAndUpdate);
            expensesInput.addEventListener('input', calculateAndUpdate);
        });
    </script>
</x-modal-form>
