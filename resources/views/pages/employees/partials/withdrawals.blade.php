<div class="card p-4 shadow-sm mb-4">
    <div class="alert-withdrawal d-flex justify-content-center align-content-center text-center mt-3 mb-3 m-auto w-25">
    </div>
    <div class="card-header bg-danger text-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0">السحوبات</h5>
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#withdrawModal">
            إضافة سحب <i class="bi bi-plus-circle"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-secondary table-bordered table-hover text-center align-middle"
                id="withdrawalsTable">
                <thead>
                    <tr class="table-dark">
                        <th>المبلغ</th>
                        <th>التاريخ</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employee->withdrawals as $withdrawal)
                        <tr id="withdrawal-{{ $withdrawal->id }}">
                            <td>{{ transform_numeric_value($withdrawal->amount) }}</td>
                            <td>{{ transform_numbers($withdrawal->date->format('d-m-Y')) }}</td>
                            <td>{{ $withdrawal->notes ?? 'لا يوجد' }}</td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-withdrawal" data-id="{{ $withdrawal->id }}"
                                    data-bs-toggle="modal" data-bs-target="#deleteWithdrawalModal">
                                    حذف<i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center" colspan="4">لا يوجد سحوبات حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Withdrawal Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawModalLabel">إضافة سحب جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="withdrawForm" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">المبلغ</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount">
                        <div class="invalid-feedback" id="amountError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">التاريخ</label>
                        <input type="date" class="globdatepicker form-control" id="date" name="date">
                        <div class="invalid-feedback" id="dateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <input type="text" class="form-control" id="holiday_notes" name="notes"></input>
                        <div class="invalid-feedback" id="notesError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="submitWithdraw">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Withdrawal Confirmation Modal -->
<div class="modal fade" id="deleteWithdrawalModal" tabindex="-1" aria-labelledby="deleteWithdrawalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteWithdrawalModalLabel">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                هل أنت متأكد من حذف هذا السحب؟
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteWithdrawal">تأكيد الحذف</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const withdrawForm = document.getElementById('withdrawForm');
        const submitWithdrawBtn = document.getElementById('submitWithdraw');
        let withdrawalToDelete = null;

        // Handle withdrawal form submission
        withdrawForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitWithdrawBtn.disabled = true;
            submitWithdrawBtn.innerHTML = 'جاري الحفظ...';

            // Reset validation
            resetValidation();

            const formData = new FormData(this);

            fetch('{{ route('employees.withdraw', $employee->id) }}', {
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
                            'withdrawModal'));
                        modal.hide();
                        withdrawForm.reset();

                        // Add new row to table
                        addWithdrawalRow(data.withdrawal);

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
                    submitWithdrawBtn.disabled = false;
                    submitWithdrawBtn.innerHTML = 'حفظ';
                });
        });

        // Handle withdrawal deletion
        document.querySelectorAll('.delete-withdrawal').forEach(button => {
            button.addEventListener('click', function() {
                withdrawalToDelete = this.getAttribute('data-id');
            });
        });

        document.getElementById('confirmDeleteWithdrawal').addEventListener('click', function() {
            if (!withdrawalToDelete) return;

            this.disabled = true;
            this.innerHTML = 'جاري الحذف...';

            fetch(`/employees/withdrawals/${withdrawalToDelete}`, {
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
                        document.getElementById(`withdrawal-${withdrawalToDelete}`).remove();

                        // Check if table is empty
                        const tbody = document.querySelector('#withdrawalsTable tbody');
                        if (tbody.children.length === 0) {
                            const emptyRow = document.createElement('tr');
                            emptyRow.innerHTML = `
                    <td class="text-center" colspan="4">لا يوجد سحوبات حتى الآن.</td>
                `;
                            tbody.appendChild(emptyRow);
                        }

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'deleteWithdrawalModal'));
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
                    withdrawalToDelete = null;
                });
        });

        function addWithdrawalRow(withdrawal) {
            const tbody = document.querySelector('#withdrawalsTable tbody');

            const emptyRow = tbody.querySelector('tr td[colspan="4"]');
            if (emptyRow) emptyRow.parentElement.remove();

            const newRow = document.createElement('tr');
            newRow.id = `withdrawal-${withdrawal.id}`;
            newRow.innerHTML = `
            <td>${transformNumbers(withdrawal.amount)} </td>
            <td>${transformNumbers(withdrawal.date.split(',')[0])}</td>
            <td>${withdrawal.notes || 'لا يوجد'}</td>
            <td>
                <button class="btn btn-danger btn-sm delete-withdrawal" 
                        data-id="${withdrawal.id}"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteWithdrawalModal">
                    حذف
                </button>
            </td>
        `;
            tbody.appendChild(newRow);

            // Add event listener to new delete button
            newRow.querySelector('.delete-withdrawal').addEventListener('click', function() {
                withdrawalToDelete = this.getAttribute('data-id');
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
                const input = document.getElementById(field);
                const errorDiv = document.getElementById(`${field}Error`);
                if (input && errorDiv) {
                    input.classList.add('is-invalid');
                    errorDiv.textContent = messages[0];
                }
            }
        }

        function transformNumbers(text) {
            // Your existing transform_numbers function implementation
            return text;
        }

        function showAlert(type, message) {
            // Create and show alert (you can use Toast or simple alert)
            const alertDiv = document.createElement('div');
            alertDiv.className =
                `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
            document.querySelector('.alert-withdrawal').prepend(alertDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    });
</script>
