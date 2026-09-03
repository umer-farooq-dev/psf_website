@extends('layouts.admin.app')

@section('title', translate('Quote_Requests'))

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                {{ translate('Quote_Requests') }}
                <span class="badge badge-soft-secondary">{{ $statusCount['all'] }}</span>
            </h2>

            <a href="{{ route('admin.psf-quote.export', ['status' => $status]) }}"
               class="btn btn-outline-primary">
                <i class="fi fi-rr-download"></i> {{ translate('Export') }}
            </a>
        </div>

        <div class="card">
            <div class="card-header flex-wrap gap-2">
                <div class="d-flex flex-wrap gap-2">
                    @php($tabs = ['' => 'all', 'nouveau' => 'nouveau', 'contacted' => 'contacted', 'closed' => 'closed'])
                    @foreach ($tabs as $key => $label)
                        <a href="{{ route('admin.psf-quote.index', ['status' => $key]) }}"
                           class="btn btn-sm {{ (string)$status === (string)$key ? 'btn--primary' : 'btn-outline-primary' }}">
                            {{ $label === 'all' ? translate('all') : translate($label) }}
                            <span class="badge bg-white text-dark ms-1">
                                {{ $label === 'all' ? $statusCount['all'] : $statusCount[$key] }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <form action="{{ route('admin.psf-quote.index') }}" method="GET" class="d-flex gap-2">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="search" name="searchValue" class="form-control"
                           value="{{ $search }}" placeholder="{{ translate('Search_by_Name_or_Email_or_Phone') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('Search') }}</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('Phone') }}</th>
                        <th>{{ translate('Client_Type') }}</th>
                        <th>{{ translate('Product_Sought') }}</th>
                        <th>{{ translate('Submitted_on') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($quotes as $key => $quote)
                        <tr class="{{ $quote->seen ? '' : 'fw-bold' }}">
                            <td>{{ $quotes->firstItem() + $key }}</td>
                            <td>
                                {{ $quote->name }}
                                @if (!$quote->seen)
                                    <span class="badge badge-soft-danger ms-1">{{ translate('New') }}</span>
                                @endif
                            </td>
                            <td><a href="tel:{{ $quote->phone }}">{{ $quote->phone }}</a></td>
                            <td>{{ $quote->client_type_label }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($quote->product_sought, 40) }}</td>
                            <td>{{ $quote->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                @php($map = ['nouveau' => 'danger', 'contacted' => 'warning', 'closed' => 'success'])
                                <span class="badge badge-soft-{{ $map[$quote->status] ?? 'secondary' }}">
                                    {{ translate($quote->status) }}
                                </span>
                            </td>
                            <td>
                                {{-- the panel's own action-button pattern: icon-btn + Flaticon --}}
                                <div class="d-flex justify-content-center gap-3">
                                    <a class="btn btn-outline-success icon-btn" title="{{ translate('View') }}"
                                       href="{{ route('admin.psf-quote.view', $quote->id) }}">
                                        <i class="fi fi-sr-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.psf-quote.delete', $quote->id) }}" method="POST"
                                          onsubmit="return confirm('{{ translate('are_you_sure_you_want_to_delete_this') }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger icon-btn"
                                                title="{{ translate('delete') }}">
                                            <i class="fi fi-rr-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                {{ translate('No_quote_request_found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($quotes->hasPages())
                <div class="card-footer">
                    {!! $quotes->links() !!}
                </div>
            @endif
        </div>
    </div>
@endsection
