@if ($paginator->hasPages())
    <nav role="navigation" class="d-flex justify-content-center mt-3">
        <ul class="pagination" dir="rtl">

            {{-- Previous Page --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">السابق</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a>
                </li>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active"><span class="page-link">{{ transform_numbers($page) }}</span>
                            </li>
                        @else
                            <li class="page-item"><a class="page-link"
                                    href="{{ $url }}">{{ transform_numbers($page) }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">التالى</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">التالى</span>
                </li>
            @endif

        </ul>
    </nav>
@endif
