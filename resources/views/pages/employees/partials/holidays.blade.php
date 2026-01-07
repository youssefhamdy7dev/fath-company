<div class="card p-4 shadow-sm mb-4">
    <div class="alert-holiday d-flex justify-content-center align-content-center text-center mt-3 mb-3 m-auto w-25">
    </div>
    <div class="card-header bg-warning text- d-flex justify-content-between align-items-center">
        <h5 class="mb-0">الأجازات</h5>
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#holidayModal">
            إضافة أجازة <i class="bi bi-plus-circle"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-secondary table-bordered table-hover text-center align-middle" id="holidaysTable">
                <thead>
                    <tr class="table-dark">
                        <th>التاريخ</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employee->holidays as $holiday)
                        <tr id="holiday-{{ $holiday->id }}">
                            <td>{{ transform_numbers($holiday->date->format('d-m-Y')) }}</td>
                            <td>{{ $holiday->notes ?? 'لا يوجد' }}</td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-holiday" data-id="{{ $holiday->id }}"
                                    data-bs-toggle="modal" data-bs-target="#deleteHolidayModal">
                                    حذف<i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center" colspan="3">لا يوجد أجازات حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="holidayModalLabel">إضافة أجازة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="holidayForm" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="holiday_date" class="form-label">التاريخ</label>
                        <input type="date" class="globdatepicker form-control" id="holiday_date" name="date">
                        <div class="invalid-feedback" id="dateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="holiday_notes" class="form-label">ملاحظات</label>
                        <input type="text" class="form-control" id="holiday_notes" name="notes"></input>
                        <div class="invalid-feedback" id="notesError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="submitHoliday">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Holiday Confirmation Modal -->
<div class="modal fade" id="deleteHolidayModal" tabindex="-1" aria-labelledby="deleteHolidayModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteHolidayModalLabel">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                هل أنت متأكد من حذف هذه الأجازة؟
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteHoliday">تأكيد الحذف</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const holidayForm = document.getElementById('holidayForm');
        const submitHolidayBtn = document.getElementById('submitHoliday');
        let holidayToDelete = null;

        // Handle holiday form submission
        holidayForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitHolidayBtn.disabled = true;
            submitHolidayBtn.innerHTML = 'جاري الحفظ...';

            // Reset validation
            resetValidation();

            const formData = new FormData(this);

            fetch('{{ route('employees.holidayAdd', $employee->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal and reset form
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'holidayModal'));
                        modal.hide();
                        holidayForm.reset();

                        // Add new row to table
                        addHolidayRow(data.holiday);

                        // Show success message
                        showAlert('success', data.message);
                    } else {
                        // Show validation errors
                        showValidationErrors(data.errors);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'حدث خطأ أثناء حفظ البيانات');
                })
                .finally(() => {
                    submitHolidayBtn.disabled = false;
                    submitHolidayBtn.innerHTML = 'حفظ';
                });
        });

        // Handle holiday deletion
        document.querySelectorAll('.delete-holiday').forEach(button => {
            button.addEventListener('click', function() {
                holidayToDelete = this.getAttribute('data-id');
            });
        });

        document.getElementById('confirmDeleteHoliday').addEventListener('click', function() {
            if (!holidayToDelete) return;

            this.disabled = true;
            this.innerHTML = 'جاري الحذف...';

            fetch(`/employees/holidays/${holidayToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row from table
                        document.getElementById(`holiday-${holidayToDelete}`).remove();

                        // Check if table is empty
                        const tbody = document.querySelector('#holidaysTable tbody');
                        if (tbody.children.length === 0) {
                            const emptyRow = document.createElement('tr');
                            emptyRow.innerHTML = `
                    <td class="text-center" colspan="3">لا يوجد أجازات حتى الآن.</td>
                `;
                            tbody.appendChild(emptyRow);
                        }

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'deleteHolidayModal'));
                        modal.hide();

                        // Show success message
                        showAlert('success', data.message);
                    } else {
                        showAlert('error', 'حدث خطأ أثناء الحذف');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'حدث خطأ أثناء الحذف');
                })
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = 'تأكيد الحذف';
                    holidayToDelete = null;
                    modal.hide();
                });
        });

        function addHolidayRow(holiday) {
            const tbody = document.querySelector('#holidaysTable tbody');

            // Remove empty message row if it exists
            const emptyRow = tbody.querySelector('tr td[colspan="3"]');
            if (emptyRow) emptyRow.parentElement.remove();

            const newRow = document.createElement('tr');
            newRow.id = `holiday-${holiday.id}`;
            newRow.innerHTML = `
            <td>${transformNumbers(holiday.date.split(',')[0])}</td>
            <td>${holiday.notes || 'لا يوجد'}</td>
            <td>
                <button class="btn btn-danger btn-sm delete-holiday" 
                        data-id="${holiday.id}"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteHolidayModal">
                    حذف
                </button>
            </td>
        `;
            tbody.appendChild(newRow);

            // Add event listener to new delete button
            newRow.querySelector('.delete-holiday').addEventListener('click', function() {
                holidayToDelete = this.getAttribute('data-id');
            });
        }

        function resetValidation() {
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(el => {
                el.textContent = '';
            });
        }

        function showValidationErrors(errors) {
            resetValidation();
            for (const [field, messages] of Object.entries(errors)) {
                const input = document.getElementById(field == 'date' ? 'holiday_date' : 'holiday_notes');
                const errorDiv = document.getElementById(`${field}Error`);
                if (input && errorDiv) {
                    input.classList.add('is-invalid');
                    errorDiv.textContent = messages[0];
                }
            }
        }

        function transformNumbers(text) {
            return text;
        }

        function showAlert(type, message) {
            // Create and show alert
            const alertDiv = document.createElement('div');
            alertDiv.className =
                `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
            document.querySelector('.alert-holiday').prepend(alertDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    });
</script>
