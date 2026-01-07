@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 mt-3">

        {{-- BACK BUTTON --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <a href="{{ route('bills.index') }}" class="btn btn-info">
                العودة إلى قائمة الفواتير <i class="bi bi-receipt ps-1"></i>
            </a>
        </div>

        {{-- EDIT BILL FORM --}}
        <form id="editBillForm" method="POST" dir="rtl" action="{{ route('bills.update', $bill->id) }}"
            class="mx-auto w-75" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="d-flex justify-content-center">
                <div class="w-50">

                    <input type="hidden" name="truck_id" value="{{ $bill->truck_id }}">

                    {{-- Billing Date --}}
                    <div class="mb-3">
                        <label class="form-label">تاريخ الصرف</label>
                        <input type="text" name="billing_date" id="billing_date" class="form-control globdatepicker"
                            value="{{ $bill->billing_date->format('Y-m-d') }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Percentage --}}
                    <div class="mb-3">
                        <label class="form-label">العمولة (%)</label>
                        <input type="number" name="percentage" id="percentage" class="form-control" min="0"
                            value="{{ $bill->percentage }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Expenses --}}
                    <div class="mb-3">
                        <label class="form-label">مصاريف / خوارج</label>
                        <input type="number" name="expenses" id="expenses" class="form-control"
                            value="{{ $bill->expenses }}" placeholder="كتابة إجمالى خوارج فقط ، التفاصيل فالملاحظات...">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="notes" id="notes" value="{{ $bill->notes }}"
                            class="form-control">
                        <div class="invalid-feedback"></div>
                    </div>

                    <input type="hidden" name="grand_total" value="{{ $bill->grand_total }}">
                </div>
            </div>

            {{-- TABLE --}}
            <h5 class="mt-4 mb-2 text-center">الفاتورة</h5>

            <div class="card shadow-sm p-4">
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

                            @foreach ($bill->items as $item)
                                <tr class="fw-bold">

                                    <td>{{ transform_numeric_value($item->total_boxes) }}</td>
                                    <td>{{ transform_numeric_value($item->total_weight) }}</td>
                                    <td>{{ transform_numbers($item->price) }}</td>
                                    <td>{{ transform_numeric_value($item->total_amount) }}</td>

                                    <input type="hidden" name="items[{{ $loop->index }}][id]"
                                        value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $loop->index }}][box_class]"
                                        value="{{ $item->box_class }}">
                                    <input type="hidden" name="items[{{ $loop->index }}][price]"
                                        value="{{ $item->price }}">
                                    <input type="hidden" name="items[{{ $loop->index }}][total_boxes]"
                                        value="{{ $item->total_boxes }}">
                                    <input type="hidden" name="items[{{ $loop->index }}][total_weight]"
                                        value="{{ $item->total_weight }}">
                                    <input type="hidden" name="items[{{ $loop->index }}][total_amount]"
                                        value="{{ $item->total_amount }}">
                                </tr>
                            @endforeach

                            {{-- TOTAL --}}
                            <tr class="table-light fw-bold fs-5">
                                <td colspan="3" class="text-danger">الإجمالي</td>
                                <td class="text-danger">{{ transform_numeric_value($bill->grand_total) }}</td>
                            </tr>

                            {{-- PERCENTAGE --}}
                            <tr class="table-light fw-bold fs-5">
                                <td colspan="3">العمولة</td>
                                <td id="percentage_value"></td>
                            </tr>

                            {{-- FREIGHT --}}
                            <tr class="table-light fw-bold fs-5">
                                <td colspan="3">النولون</td>
                                <td>{{ transform_numeric_value($bill->truck->freight) }}</td>
                            </tr>

                            {{-- EXPENSES --}}
                            <tr class="table-light fw-bold fs-5">
                                <td colspan="3">الخوارج / المصاريف</td>
                                <td id="expenses_value"></td>
                            </tr>

                            {{-- FINAL TOTAL --}}
                            <tr class="table-light fw-bold fs-4">
                                <td colspan="3" class="text-success">صافى الفاتورة</td>
                                <td class="text-success" id="finalTotal"></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SUBMIT --}}
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    حفظ التعديلات <i class="bi bi-save ps-1"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- AJAX SUBMIT --}}
    <script>
        document.getElementById('editBillForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const url = form.getAttribute('action');

            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            const btn = form.querySelector('button[type="submit"]');
            const original = btn.innerHTML;

            btn.classList.add('disabled');
            btn.innerHTML = 'جارٍ الحفظ...';

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
                        btn.classList.remove('disabled');
                        btn.innerHTML = original;
                    } else {
                        window.location.href = r.redirect_url;
                    }
                })
                .catch(() => {
                    alert('خطأ أثناء الحفظ');
                    btn.classList.remove('disabled');
                    btn.innerHTML = original;
                });
        });
    </script>

    {{-- LIVE CALCULATIONS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const grandTotal = {{ $bill->grand_total }};
            const freight = {{ $bill->truck->freight }};

            const percentageInput = document.getElementById('percentage');
            const expensesInput = document.getElementById('expenses');

            const percentageTd = document.getElementById('percentage_value');
            const expensesTd = document.getElementById('expenses_value');
            const finalTd = document.getElementById('finalTotal');

            const transformNumbers = (num) => {
                const w = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                const e = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                return String(num).split('').map(ch => w.includes(ch) ? e[w.indexOf(ch)] : ch).join('');
            };

            const update = () => {
                const p = parseFloat(percentageInput.value) || 0;
                const ex = parseFloat(expensesInput.value) || 0;

                const pValue = Math.floor((p / 100) * grandTotal);
                const exValue = Math.floor(ex);

                const final = grandTotal - (freight + pValue + exValue);

                percentageTd.textContent = transformNumbers(pValue);
                expensesTd.textContent = transformNumbers(exValue);
                finalTd.textContent = transformNumbers(final);
            };

            update();
            percentageInput.addEventListener('input', update);
            expensesInput.addEventListener('input', update);
        });
    </script>
@endsection
