@extends('layouts.admin.app')

@section('title', translate('Our_Realisations'))

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                {{ translate('Our_Realisations') }}
                <span class="badge badge-soft-secondary">{{ $total }}</span>
            </h2>

            <a href="{{ route('admin.psf-gallery.create') }}" class="btn btn--primary">
                <i class="tio-add"></i> {{ translate('Add_a_Realisation') }}
            </a>
        </div>

        <div class="card">
            <div class="card-header flex-wrap gap-2">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.psf-gallery.index') }}"
                       class="btn btn-sm {{ $category ? 'btn-outline-primary' : 'btn--primary' }}">
                        {{ translate('all') }}
                    </a>
                    @foreach ($categories as $option)
                        <a href="{{ route('admin.psf-gallery.index', ['category' => $option]) }}"
                           class="btn btn-sm {{ $category === $option ? 'btn--primary' : 'btn-outline-primary' }}">
                            {{ $option }}
                        </a>
                    @endforeach
                </div>

                <form action="{{ route('admin.psf-gallery.index') }}" method="GET" class="d-flex gap-2">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <input type="search" name="searchValue" class="form-control"
                           value="{{ $search }}" placeholder="{{ translate('Search_by_title_or_location') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('Search') }}</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('image') }}</th>
                        <th>{{ translate('Title') }}</th>
                        <th>{{ translate('category') }}</th>
                        <th>{{ translate('Location') }}</th>
                        <th>{{ translate('Completed_on') }}</th>
                        <th>{{ translate('Order') }}</th>
                        <th class="text-center">{{ translate('status') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($items as $key => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $key }}</td>
                            <td>
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->alt }}"
                                         class="rounded" width="56" height="56"
                                         style="object-fit: cover;">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($item->title, 45) }}</td>
                            <td>{{ $item->category ?: '—' }}</td>
                            <td>{{ $item->location ?: '—' }}</td>
                            <td>{{ $item->completed_on?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $item->sort_order }}</td>
                            <td class="text-center">
                                <form action="{{ route('admin.psf-gallery.status', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $item->status ? 'btn-outline-success' : 'btn-outline-secondary' }}">
                                        {{ $item->status ? translate('Visible') : translate('Hidden') }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.psf-gallery.edit', $item->id) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.psf-gallery.delete', $item->id) }}" method="POST"
                                          onsubmit="return confirm('{{ translate('are_you_sure_you_want_to_delete_this') }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="tio-delete"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                {{ translate('No_realisation_found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="card-footer">
                    {!! $items->links() !!}
                </div>
            @endif
        </div>
    </div>
@endsection
