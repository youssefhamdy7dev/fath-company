@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center mt-3">
        <x-alerts.session-status />

        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('clients.create') }}" color="success">
                إضافة عميل جديد <i class="bi bi-person-fill-add"></i>
            </x-button>
        </div>

        <h1 class="mb-5">قائمة العملاء</h1>

        <table class="table table-success table-bordered table-hover align-middle">
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
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ transform_numeric_value($loop->iteration) }}</td>
                        <td>{{ $client->name }}</td>
                        <td class="{{ $client->phone ? '' : 'text-muted' }}">
                            {{ $client->phone ?? 'لم يتم تسجيل رقم الهاتف' }}</td>
                        <td>{{ transform_numbers($client->created_at->format('d-m-Y')) }}</td>
                        <td>
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-info btn-sm">عرض <i
                                    class="ps-1 bi bi-eye-fill"></i></a>
                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning btn-sm">تعديل <i
                                    class="bi bi-pencil-square"></i></a>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $client->id }}"
                                data-name="{{ $client->name }}" data-bs-toggle="modal" data-bs-target="#deleteClientModal">
                                حذف<i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted">لم يتم إضافة أى عميل.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Single Reusable Delete Modal --}}
    <x-modal id="deleteClientModal" title="تأكيد الحذف" body="هل أنت متأكد من حذف هذا الصنف؟" confirmText="حذف"
        confirmButtonClass="btn-danger btn-reload" />

    {{-- Hidden form for deletion --}}
    <form id="deleteClientForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => initDeleteHandler('client'));
    </script>
@endsection
