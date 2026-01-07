@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center mt-3">
        <x-alerts.session-status />

        {{-- Add Driver Button --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('drivers.create') }}" color="success">
                إضافة سائق جديد <i class="ps-1 bi-person-video2"></i>
            </x-button>
        </div>

        <h1 class="mb-5">قائمة السائقون</h1>

        <table class="table table-warning table-bordered table-hover align-middle">
            <thead>
                <tr class="table-dark">
                    <th>#</th>
                    <th>الاسم</th>
                    <th>رقم الهاتف</th>
                    <th>تاريخ التسجيل</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($drivers as $driver)
                    <tr>
                        <td>{{ transform_numeric_value($loop->iteration) }}</td>
                        <td>{{ $driver->name }}</td>
                        <td class="{{ $driver->phone ? '' : 'text-muted' }}">
                            {{ $driver->phone ?? 'لم يتم تسجيل رقم الهاتف' }}</td>
                        <td>{{ transform_numbers($driver->created_at->format('d-m-Y')) }}</td>
                        <td>
                            <a href="{{ route('drivers.show', $driver) }}" class="btn btn-info btn-sm">عرض <i
                                    class="ps-1 bi bi-eye-fill"></i></a>
                            <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-warning btn-sm">تعديل <i
                                    class="bi bi-pencil-square"></i></a>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $driver->id }}"
                                data-name="{{ $driver->name }}" data-bs-toggle="modal" data-bs-target="#deleteDriverModal">
                                حذف<i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted">لم يتم إضافة أى سائق حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Single Reusable Delete Modal --}}
    <x-modal id="deleteDriverModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا السائق؟" confirmText="حذف"
        confirmButtonClass="btn-danger btn-reload" />

    {{-- Hidden form for deletion --}}
    <form id="deleteDriverForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => initDeleteHandler('driver'));
    </script>
@endsection
