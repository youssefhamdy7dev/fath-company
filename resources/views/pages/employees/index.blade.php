@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center mt-3">
        {{-- Session alerts --}}
        <x-alerts.session-status />

        {{-- Add New Employee --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('employees.create') }}" color="success">
                إضافة موظف جديد <i class="bi bi-person-plus-fill"></i>
            </x-button>
        </div>

        <h1 class="mb-5">قائمة الموظفين</h1>

        <table class="table table-primary table-bordered table-hover align-middle">
            <thead>
                <tr class="table-dark">
                    <th>#</th>
                    <th>الاسم</th>
                    <th>اليومية</th>
                    <th>تاريخ البداية</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ transform_numeric_value($loop->iteration) }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ transform_numeric_value($employee->payment) }}</td>
                        <td>{{ transform_numbers($employee->start_date->format('d-m-Y')) }}</td>
                        <td class="d-flex justify-content-center gap-1">
                            {{-- Show --}}
                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-info btn-sm">
                                عرض <i class="bi bi-eye-fill"></i>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">
                                تعديل <i class="bi bi-pencil-square"></i>
                            </a>

                            {{-- Delete --}}
                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $employee->id }}"
                                data-name="{{ $employee->name }}" data-bs-toggle="modal"
                                data-bs-target="#deleteEmployeeModal">
                                حذف<i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted text-center">لم يتم تسجيل أى موظفين حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Single Reusable Delete Modal --}}
    <x-modal id="deleteEmployeeModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا الموظف" confirmText="حذف"
        confirmButtonClass="btn-danger btn-reload" />

    {{-- Hidden form for deletion --}}
    <form id="deleteEmployeeForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => initDeleteHandler('employee'));
    </script>
@endsection
