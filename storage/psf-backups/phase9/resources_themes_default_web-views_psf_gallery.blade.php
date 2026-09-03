@extends('layouts.front-end.app')

@section('title', translate('Our_Realisations'))

@section('content')
    <div class="container my-4 rtl text-align-direction">

        <div class="text-center mb-4">
            <h2 class="mb-2">{{ translate('Our_Realisations') }}</h2>
            <p class="text-muted mb-0">
                {{ translate('a_few_projects_carried_out_by_our_teams') }}
            </p>
        </div>

        @if (count($categories) > 0)
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <a href="{{ route('psf.gallery.index') }}"
                   class="btn btn-sm {{ $category ? 'btn-outline-primary' : 'btn--primary' }}">
                    {{ translate('all') }}
                </a>
                @foreach ($categories as $option)
                    <a href="{{ route('psf.gallery.index', ['category' => $option]) }}"
                       class="btn btn-sm {{ $category === $option ? 'btn--primary' : 'btn-outline-primary' }}">
                        {{ $option }}
                    </a>
                @endforeach
            </div>
        @endif

        @if (count($items) > 0)
            <div class="row g-3">
                @foreach ($items as $item)
                    <div class="col-lg-4 col-sm-6">
                        <div class="card h-100">
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->alt }}"
                                     class="card-img-top" loading="lazy"
                                     style="aspect-ratio: 4 / 3; object-fit: cover;">
                            @endif
                            <div class="card-body">
                                <h5 class="mb-2">{{ $item->title }}</h5>

                                @if ($item->category || $item->location)
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @if ($item->category)
                                            <span class="badge badge-soft-primary">{{ $item->category }}</span>
                                        @endif
                                        @if ($item->location)
                                            <span class="badge badge-soft-secondary">{{ $item->location }}</span>
                                        @endif
                                    </div>
                                @endif

                                @if ($item->description)
                                    <p class="text-muted mb-2 fs-12">
                                        {{ \Illuminate\Support\Str::limit($item->description, 160) }}
                                    </p>
                                @endif

                                @if ($item->completed_on)
                                    <div class="fs-12 text-muted">
                                        {{ translate('Completed_on') }} : {{ $item->completed_on->format('d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($items->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {!! $items->links() !!}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <p class="text-muted mb-3">{{ translate('No_realisation_found') }}</p>
            </div>
        @endif

        @if (psfQuoteMenu())
            <div class="text-center mt-4">
                <a href="{{ route('psf.quote.index') }}" class="btn btn--primary">
                    {{ translate('Request_a_Quote') }}
                </a>
            </div>
        @endif
    </div>
@endsection
