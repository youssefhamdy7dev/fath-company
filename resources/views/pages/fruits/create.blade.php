@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">
        {{-- Back to list button --}}
        <div class="d-flex justify-content-end w-100">
            <x-button href="{{ route('fruits.index') }}" color="info">
                العودة إلى قائمة الأصناف <i class="bi bi-boxes"></i>
            </x-button>
        </div>

        <h3 class="mb-4">إضافة صنف جديد</h3>

        {{-- Session Alert --}}
        <x-alerts.session-status />

        {{-- Form --}}
        <form method="POST" action="{{ route('fruits.store') }}" enctype="multipart/form-data" dir="rtl"
            class="form-control" autocomplete="off">
            @csrf
            <div class="container w-50">
                {{-- Name --}}
                <div class="form-div">
                    <label for="name" class="form-label">إسم الصنف</label>
                    <input type="text" name="name" id="name" placeholder="أدخل إسم الصنف..."
                        value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback text-danger ms-3">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Image --}}
                <div class="form-div">
                    <label for="image" class="form-label">إرفاق صورة</label>

                    <!-- hidden file input -->
                    <input type="file" name="image" id="image"
                        class="form-control d-none @error('image') is-invalid @enderror" accept="image/*">

                    <!-- main upload button -->
                    <button type="button" id="customFileButton" class="btn btn-outline-primary w-100">
                        إرفاق صورة <i class="ps-1 bi bi-upload"></i>
                    </button>

                    <!-- cancel button -->
                    <button type="button" id="cancelFileButton" class="btn btn-danger w-100 mt-2 d-none">
                        إزالة الصورة <i class="ps-1 bi bi-x-circle"></i>
                    </button>

                    <!-- file info -->
                    <div id="fileName" class="form-text text-muted mt-2 @error('image') d-none @enderror text-center">
                        لم يتم إرفاق صورة
                    </div>

                    <!-- Validation error -->
                    @error('image')
                        <div class="invalid-feedback d-block text-danger ms-3">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="text-center mt-4 mb-3">
                    <button type="submit" class="btn btn-reload btn-primary w-50">
                        إضافة الصنف<i class="ps-1 bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- File upload script --}}
    <script>
        const fileInput = document.getElementById('image');
        const fileButton = document.getElementById('customFileButton');
        const cancelButton = document.getElementById('cancelFileButton');
        const fileName = document.getElementById('fileName');

        fileButton.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileName.textContent = 'تم إرفاق: ' + fileInput.files[0].name;
                fileButton.disabled = true;
                cancelButton.classList.remove('d-none');
            } else {
                resetFileInput();
            }
        });

        cancelButton.addEventListener('click', resetFileInput);

        function resetFileInput() {
            fileInput.value = '';
            fileName.textContent = 'لم يتم إرفاق صورة';
            fileButton.disabled = false;
            cancelButton.classList.add('d-none');
        }
    </script>
@endsection
