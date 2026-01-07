<div class="modal fade" id="monthlyWageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">تصفية حساب <strong class="text-primary">{{ $employee->name }}</strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- PREVIEW FORM --}}
                <form method="POST" action="{{ route('employees.monthlyWage.preview', $employee->id) }}"
                    autocomplete="off" dir="rtl">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">آخر يوم عمل</label>
                        <input type="text" name="end_date" class="form-control globdatepicker"
                            value="{{ old('end_date', session('wage_preview.end_date')?->format('Y-m-d')) }}">
                    </div>

                    <button class="btn btn-info btn-reload">
                        مراجعة الحساب
                    </button>
                </form>
                {{-- PREVIEW RESULT --}}
                @if (session('wage_preview'))
                    @php($w = session('wage_preview'))
                    <hr>
                    <ul class="list-group">
                        <li class="list-group-item">
                            أول يوم عمل:
                            <strong>{{ transform_numbers($employee->start_date->format('d-m-Y')) }}</strong>
                        </li>
                        <li class="list-group-item">
                            إجمالى عدد الأيام:
                            <strong>{{ transform_numbers($w['total_days']) }}</strong>
                        </li>
                        <li class="list-group-item">
                            عدد أيام الأجازات:
                            <strong>{{ transform_numbers($w['holiday_count']) }}</strong>
                        </li>
                        <li class="list-group-item">
                            عدد أيام العمل:
                            <strong>{{ transform_numbers($w['working_days']) }}</strong>
                        </li>
                        <li class="list-group-item">
                            إجمالي المستحق:
                            <strong class="text-primary-emphasis">{{ transform_numeric_value($w['wage']) }}</strong>
                        </li>
                        <li class="list-group-item">
                            إجمالي السحوبات:
                            <strong
                                class="text-danger-emphasis">{{ transform_numeric_value($w['withdraw_sum']) }}</strong>
                        </li>
                        @if ($w['holiday_deduction'] > 0)
                            <li class="list-group-item">
                                خصومات الاجازات:
                                <strong
                                    class="text-danger-emphasis">{{ transform_numeric_value($w['holiday_deduction']) }}</strong>
                            </li>
                        @endif
                        <li class="list-group-item">
                            صافي المستحق:
                            <strong class="{{ $w['final_salary'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ transform_numeric_value($w['final_salary']) }}
                            </strong>
                        </li>
                        @if ($employee->remaining_withdrawal > 0)
                            <li class="list-group-item">
                                باقى له (قديم):
                                <strong class="text-success">
                                    {{ transform_numeric_value($employee->remaining_withdrawal) }}
                                </strong>
                            </li>
                            <li class="list-group-item">
                                الحساب النهائى:
                                <strong class="text-primary">
                                    {{ transform_numeric_value($w['final_salary'] + $employee->remaining_withdrawal) }}
                                </strong>
                            </li>
                        @elseif ($employee->over_withdrawal_limit > 0)
                            <li class="list-group-item">
                                باقى عليه (قديم):
                                <strong class="text-danger">
                                    {{ transform_numeric_value(-$employee->over_withdrawal_limit) }}
                                </strong>
                            </li>
                            <li class="list-group-item">
                                الحساب النهائى:
                                <strong class="text-primary">
                                    {{ transform_numeric_value($w['final_salary'] - $employee->over_withdrawal_limit) }}
                                </strong>
                            </li>
                        @endif
                    </ul>
                    {{-- SAVE --}}
                    <form method="POST" action="{{ route('employees.monthlyWage', $employee->id) }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="end_date" value="{{ $w['end_date']->format('Y-m-d') }}">
                        <button class="btn btn-success btn-reload">
                            تأكيد الحفظ
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>
