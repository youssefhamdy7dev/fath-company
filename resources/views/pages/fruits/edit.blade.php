@extends('layouts.app')

@section('content')
    <div class="card shadow-sm p-4 align-items-center align-content-center text-center">
        {{-- Back to index --}}
        <div class="d-flex justify-content-end w-100 mb-3">
            <x-button href="{{ route('fruits.index') }}" color="info">
                العودة إلى قائمة الأصناف <i class="bi bi-boxes"></i>
            </x-button>
        </div>

        <h3 class="mb-4">تعديل الصنف</h3>

        <form method="POST" action="{{ route('fruits.update', $fruit->id) }}" enctype="multipart/form-data"
            class="form-control" dir="rtl" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="container w-50">

                {{-- Name --}}
                <x-form.input id="name" name="name" label="إسم الصنف" value="{{ old('name', $fruit->name) }}" />

                {{-- Image Upload --}}
                <div class="form-div mb-3">

                    {{-- Current image preview --}}
                    <div class="d-flex flex-column align-items-center mb-2">
                        <label>الصورة الحالية</label>
                        @if ($fruit->image && Storage::disk('public')->exists('fruits/' . $fruit->image))
                            <img src="{{ asset('storage/fruits/' . $fruit->image) }}" alt="{{ $fruit->name }}"
                                class="img-thumbnail" style="max-width: 400px;">
                        @else
                            <p class="text-muted">لم يتم إرفاق صورة</p>
                        @endif
                    </div>


                    <label for="image" class="form-label fw-semibold">إرفاق صورة جديدة</label>

                    {{-- Hidden file input --}}
                    <input type="file" name="image" id="image"
                        class="form-control d-none @error('image') is-invalid @enderror" accept="image/*">

                    {{-- Upload button --}}
                    <button type="button" id="customFileButton" class="btn btn-outline-primary w-100 mb-2">
                        إرفاق صورة <i class="ps-1 bi bi-upload"></i>
                    </button>

                    {{-- Cancel button --}}
                    <button type="button" id="cancelFileButton" class="btn btn-outline-danger w-100 mb-2 d-none">
                        إزالة الصورة <i class="ps-1 bi bi-x-circle"></i>
                    </button>

                    {{-- File info --}}
                    <div id="fileName" class="form-text text-center text-muted mt-2">
                        اختر صورة جديدة لتغيير الصورة الحالية
                    </div>

                    @error('image')
                        <div class="invalid-feedback d-block text-danger text-end">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="text-center">
                    <button type="submit" class="btn btn-reload btn-primary w-50">
                        حفظ التعديلات <i class="bi bi-save"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- JS for file upload button --}}
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

        cancelButton.addEventListener('click', () => resetFileInput());

        function resetFileInput() {
            fileInput.value = '';
            fileName.textContent = 'اختر صورة جديدة لتغيير الصورة الحالية';
            fileButton.disabled = false;
            cancelButton.classList.add('d-none');
        }
    </script>
@endsection
