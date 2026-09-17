{{-- PSF · pagination in the template style ("pagination style-1": 1 2 3 Next).
     Used as $paginator->links('web-views.partials._psf-pagination'). --}}
@if ($paginator->hasPages())
    <nav aria-label="{{ translate('pagination') }}">
        <ul class="pagination style-1 p-t20 psf-pagination">
            @if (!$paginator->onFirstPage())
                <li class="page-item">
                    <a class="page-link prev" href="{{ $paginator->previousPageUrl() }}" rel="prev" data-page="{{ $paginator->currentPage() - 1 }}">{{ translate('prev') }}</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link next" href="{{ $paginator->nextPageUrl() }}" rel="next" data-page="{{ $paginator->currentPage() + 1 }}">{{ translate('next') }}</a>
                </li>
            @endif
        </ul>
    </nav>
@endif
