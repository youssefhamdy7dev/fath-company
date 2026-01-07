@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">
        {{-- Alerts --}}
        <x-alerts.session-status />

        {{-- Add New Fruit Button --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('fruits.create') }}" color="success">
                إضافة صنف جديد <i class="bi bi-box2-fill"></i>
            </x-button>
        </div>

        <h1 class="mb-5">قائمة الأصناف</h1>

        <table class="table table-info table-bordered table-hover align-middle">
            <thead>
                <tr class="table-dark">
                    <th>#</th>
                    <th>إسم الصنف</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fruits as $index => $fruit)
                    <tr>
                        <th scope="row">{{ transform_numeric_value($loop->iteration) }}</th>
                        <td>{{ $fruit->name }}</td>
                        <td class="d-flex justify-content-center gap-1">
                            {{-- Show --}}
                            <a href="{{ route('fruits.show', $fruit->id) }}" class="btn btn-info btn-sm">
                                عرض <i class="bi bi-eye-fill"></i>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('fruits.edit', $fruit->id) }}" class="btn btn-warning btn-sm">
                                تعديل <i class="bi bi-pencil-square"></i>
                            </a>

                            {{-- Delete --}}
                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $fruit->id }}"
                                data-name="{{ $fruit->name }}" data-bs-toggle="modal" data-bs-target="#deleteFruitModal">
                                حذف<i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">لا يوجد أصناف مضافة حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Single Reusable Delete Modal --}}
    <x-modal id="deleteFruitModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا الصنف؟" confirmText="حذف"
        confirmButtonClass="btn-reload btn-danger" />

    {{-- Hidden form for deletion --}}
    <form id="deleteFruitForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => initDeleteHandler('fruit'));
    </script>
@endsection
