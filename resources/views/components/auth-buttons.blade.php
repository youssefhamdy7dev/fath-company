<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
    @guest
        <li class="nav-item">
            <button class="btn btn-outline-light" type="button" onclick="location.href='{{ route('login') }}'">
                تسجيل الدخول
                <i class="ps-1 bi bi-box-arrow-in-left"></i>
            </button>
        </li>
    @endguest

    @auth
        <li class="nav-item dropdown">
            <a class="nav-link text-white dropdown-toggle" href="#" id="userMenu" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                {{ auth()->user()->name }}
            </a>
            <ul class="dropdown-menu" aria-labelledby="userMenu">
                <li>
                    <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            تسجيل الخروج <i class="ps-1 bi bi-box-arrow-left"></i>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    @endauth
</ul>
