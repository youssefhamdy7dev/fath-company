@extends('layouts.app')

@section('content')
    <div class="container" dir="rtl">
        <x-alerts.session-status />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">تحصيل تاريخ:
                {{ transform_numbers($displayDate->locale('ar')->translatedFormat('l ( d-m-Y )')) }}</h3>
            <div>
                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addCustomerPaymentModal">
                    إضافة تحصيل زبون<i class="ps-1 bi bi-plus-circle-fill"></i>
                </button>
                <a href="{{ route('customer-payments.index') }}" class="btn btn-dark">
                    العودة إلى القائمة السابقة<i class="ps-1 bi bi-arrow-up-left"></i></a>
            </div>
        </div>

        <div class="d-flex gap-2 mb-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0">الإسم:</label>
                <input type="text" id="searchName" class="form-control" placeholder="ابحث بالاسم"
                    style="max-width:300px;">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0">المنطقة:</label>
                <select id="filterLocation" class="form-select" style="max-width:200px;">
                    <option value="">الكل</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location }}">{{ $location }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="paymentsByDateWrapper">
            @include('pages.payments.partials.payments-by-date-table', ['grouped' => $grouped])
        </div>
    </div>
    {{-- Add Payment Modal --}}
    @include('pages.payments.partials.add-payment-modal', ['pageDate' => $displayDate])

    <x-scroll-button />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== FILTERING FUNCTIONALITY =====
            const nameInput = document.getElementById('searchName');
            const locSelect = document.getElementById('filterLocation');

            if (nameInput && locSelect) {
                nameInput.addEventListener('input', fetchFilteredTable);
                locSelect.addEventListener('change', fetchFilteredTable);
            }

            async function fetchFilteredTable() {
                const wrapper = document.getElementById('paymentsByDateWrapper');
                if (!wrapper) return;

                const params = new URLSearchParams();
                if (nameInput.value) params.append('name', nameInput.value);
                if (locSelect.value) params.append('location', locSelect.value);

                try {
                    const response = await fetch(
                        `{{ route('customer-payments.byDate', ['date' => $carbonDate]) }}?${params.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                    const html = await response.text();
                    wrapper.innerHTML = html;

                    // Re-wire delete buttons after AJAX reload
                    wireDeleteButtons();
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        });
    </script>
@endsection
