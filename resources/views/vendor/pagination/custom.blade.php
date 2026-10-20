@if($paginator->hasPages())
<nav aria-label="Pagination">
    <ul class="pagination">
        @if($paginator->onFirstPage())
            <li class="disabled"><span aria-hidden="true"><i class="fas fa-chevron-left"></i></span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="fas fa-chevron-left"></i></a></li>
        @endif

        @foreach($elements as $element)
            @if(is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif
            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $paginator->currentPage())
                        <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><i class="fas fa-chevron-right"></i></a></li>
        @else
            <li class="disabled"><span aria-hidden="true"><i class="fas fa-chevron-right"></i></span></li>
        @endif
    </ul>
</nav>
@endif
