<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container container-narrow">
        <a class="navbar-brand brand" href="{{ url('/') }}">شركة الفتح</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            {{-- Left side nav --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <x-nav-item>
                    <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                        <i class="bi bi-house-door-fill me-1"></i> الرئيسية
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('fruits.index') }}" :active="request()->routeIs('fruits.*')">
                        <i class="bi bi-box-seam-fill me-1 text-info"></i> الأصناف
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('clients.index') }}" :active="request()->routeIs('clients.*')">
                        <i class="bi bi-person-badge-fill me-1 text-warning"></i> العملاء
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('drivers.index') }}" :active="request()->routeIs('drivers.*')">
                        <i class="bi bi-person-video2 me-1 text-warning-emphasis"></i> السائقون
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('employees.index') }}" :active="request()->routeIs('employees.*')">
                        <i class="bi bi-person-gear me-1 text-primary-emphasis"></i> الموظفون
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">
                        <i class="bi bi-people-fill me-1 text-primary"></i> الزبائن
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('trucks.index') }}" :active="request()->routeIs('trucks.*')">
                        <i class="bi bi-truck-front-fill me-1 text-success"></i> العربات
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('customer-payments.index') }}" :active="request()->routeIs('customer-payments.*')">
                        <i class="bi bi-cash-stack me-1 text-info-emphasis"></i> التحصيل
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="{{ route('bills.index') }}" :active="request()->routeIs('bills.*')">
                        <i class="bi bi-receipt-cutoff me-1 text-danger-emphasis"></i> الفواتير
                    </x-nav-link>
                </x-nav-item>

                <x-nav-item>
                    <x-nav-link href="#" :active="request()->routeIs('strawberries.*')">
                        <i class="bi bi-basket2 me-1 text-danger"></i> الفراولة
                    </x-nav-link>
                </x-nav-item>
            </ul>

            {{-- Right side auth --}}
            {{-- <x-auth-buttons /> --}}
        </div>
    </div>
</nav>
