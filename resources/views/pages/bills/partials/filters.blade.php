<div class="row g-3 w-100 mb-4 text-start">

    {{-- Start date --}}
    <div class="col-md-6">
        <label for="billDateFrom" class="form-label">
            من تاريخ
        </label>

        <input type="date" id="billDateFrom" class="globdatepicker form-control">
    </div>

    {{-- End date --}}
    <div class="col-md-6">
        <label for="billDateTo" class="form-label">
            إلى تاريخ
        </label>

        <input type="date" id="billDateTo" class="globdatepicker form-control">
    </div>

    {{-- Clients --}}
    <div class="col-md-6">
        <label class="form-label">العملاء</label>

        <div class="dropdown bill-filter-dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                id="clientsDropdownButton" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                العملاء
                <span id="clientsSelectionCount" class="ms-1 text-muted">
                    (الكل)
                </span>
            </button>

            <div class="dropdown-menu w-100 p-2 bill-filter-menu">
                <div class="d-flex justify-content-between mb-2">
                    <button type="button" class="btn btn-sm btn-link p-0" id="selectAllClients">
                        تحديد الكل
                    </button>

                    <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearClientsSelection">
                        إلغاء التحديد
                    </button>
                </div>

                <hr class="my-2">

                <div class="bill-filter-options">
                    @foreach ($clientOptions as $client)
                        <div class="form-check">
                            <input class="form-check-input bill-client-checkbox" type="checkbox"
                                value="{{ $client->id }}" id="billClient{{ $client->id }}" checked>

                            <label class="form-check-label" for="billClient{{ $client->id }}">
                                {{ $client->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Fruits --}}
    <div class="col-md-6">
        <label class="form-label">الأصناف</label>

        <div class="dropdown bill-filter-dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                id="fruitsDropdownButton" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                الأصناف
                <span id="fruitsSelectionCount" class="ms-1 text-muted">
                    (الكل)
                </span>
            </button>

            <div class="dropdown-menu w-100 p-2 bill-filter-menu">
                <div class="d-flex justify-content-between mb-2">
                    <button type="button" class="btn btn-sm btn-link p-0" id="selectAllFruits">
                        تحديد الكل
                    </button>

                    <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearFruitsSelection">
                        إلغاء التحديد
                    </button>
                </div>

                <hr class="my-2">

                <div class="bill-filter-options">
                    @foreach ($fruits as $fruit)
                        <div class="form-check">
                            <input class="form-check-input bill-fruit-checkbox" type="checkbox"
                                value="{{ $fruit->id }}" id="billFruit{{ $fruit->id }}" checked>

                            <label class="form-check-label" for="billFruit{{ $fruit->id }}">
                                {{ $fruit->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Reset --}}
    <div class="col-12 text-end">
        <button type="button" id="resetBillFilters" class="btn btn-outline-danger">
            إلغاء البحث <i class="ps-1 bi bi-x-circle"></i>
        </button>
    </div>
</div>

<style>
    .bill-filter-menu {
        max-height: 280px;
        overflow-y: auto;
    }

    .bill-filter-options {
        max-height: 210px;
        overflow-y: auto;
    }

    .bill-filter-options .form-check {
        padding: 5px 28px 5px 0;
    }

    .bill-filter-options .form-check-input {
        float: right;
        margin-right: -1.5em;
        margin-left: 0;
    }

    .bill-filter-options .form-check-label {
        display: block;
        cursor: pointer;
    }
</style>
