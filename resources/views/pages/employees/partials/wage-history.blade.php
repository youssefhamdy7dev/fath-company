<h3 class="text-center mb-4">سجل تصفية الحساب</h3>
@if ($employee->monthlyWages->isEmpty())
    <div class="alert alert-info text-center">
        لا يوجد سجل تصفية حساب لهذا الموظف بعد.
    </div>
@else
    <div class="d-flex justify-content-end mb-2">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteWagesHistoryModal">
            حذف جميع التصفيات <i class="bi bi-trash"></i>
        </button>
    </div>
    <form id="deleteWagesHistory" action="{{ route('employees.clearWagesHistory', $employee->id) }}" method="POST">
        @csrf
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>الفترة</th>
                    <th>آخر يوم عمل</th>
                    <th>صافي المستحق</th>
                    <th>تاريخ التصفية</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employee->monthlyWages as $index => $wage)
                    <tr>
                        <td>{{ transform_numbers($index + 1) }}</td>

                        <td>
                            {{ transform_numbers($wage->employee_start_date->format('d-m-Y')) }}
                            ←
                            {{ transform_numbers($wage->end_date->format('d-m-Y')) }}
                        </td>

                        <td>{{ transform_numbers($wage->end_date->format('d-m-Y')) }}</td>

                        <td class="{{ $wage->total_wage < 0 ? 'text-danger' : 'text-success' }}">
                            {{ transform_numeric_value($wage->total_wage) }}
                        </td>

                        <td>
                            {{ transform_numbers($wage->created_at->translatedFormat('d-m-Y h:i A')) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-modal id="deleteWagesHistoryModal" title="تأكيد الحذف"
        body="هل أنت متأكد من حذف جميع سجلات تصفية الحساب لهذا الموظف؟ لا يمكن التراجع عن هذا الإجراء."
        confirmText="تأكيد" cancelText="إلغاء" confirmButtonClass="btn-danger btn-reload" />
    <script>
        document
            .getElementById('deleteWagesHistoryModalConfirm')
            ?.addEventListener('click', function() {
                document.getElementById('deleteWagesHistory').submit();
            });
    </script>
@endif
