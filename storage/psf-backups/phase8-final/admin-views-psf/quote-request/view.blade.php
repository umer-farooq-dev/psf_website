@extends('layouts.admin.app')

@section('title', translate('Quote_Request') . ' #' . $quote->id)

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
            <h2 class="h1 mb-0">
                {{ translate('Quote_Request') }} #{{ $quote->id }}
            </h2>
            <a href="{{ route('admin.psf-quote.index') }}" class="btn btn-outline-primary">
                {{ translate('Back_to_List') }}
            </a>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tbody>
                            <tr>
                                <th class="w-35">{{ translate('Name') }}</th>
                                <td>{{ $quote->name }}</td>
                            </tr>
                            <tr>
                                <th>{{ translate('Phone') }}</th>
                                <td><a href="tel:{{ $quote->phone }}">{{ $quote->phone }}</a></td>
                            </tr>
                            @if ($quote->whatsapp)
                                <tr>
                                    <th>WhatsApp</th>
                                    <td>
                                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $quote->whatsapp) }}"
                                           target="_blank" rel="noopener">{{ $quote->whatsapp }}</a>
                                    </td>
                                </tr>
                            @endif
                            @if ($quote->email)
                                <tr>
                                    <th>{{ translate('Email') }}</th>
                                    <td><a href="mailto:{{ $quote->email }}">{{ $quote->email }}</a></td>
                                </tr>
                            @endif
                            <tr>
                                <th>{{ translate('Client_Type') }}</th>
                                <td>{{ $quote->client_type_label ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>{{ translate('Product_Sought') }}</th>
                                <td>{{ $quote->product_sought ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>{{ translate('Quantity') }}</th>
                                <td>{{ $quote->quantity ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>{{ translate('Message') }}</th>
                                <td class="text-break">{!! nl2br(e($quote->message)) ?: '—' !!}</td>
                            </tr>
                            @if ($quote->attachment)
                                <tr>
                                    <th>{{ translate('Attachment') }}</th>
                                    <td>
                                        <a href="{{ $quote->attachment_url }}" target="_blank" rel="noopener"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="tio-download-to"></i> {{ translate('Download') }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>{{ translate('Submitted_on') }}</th>
                                <td>{{ $quote->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <form action="{{ route('admin.psf-quote.note', $quote->id) }}" method="POST">
                            @csrf
                            <label class="form-label" for="admin_note">{{ translate('Admin_Note') }}</label>
                            <textarea name="admin_note" id="admin_note" rows="3"
                                      class="form-control mb-2">{{ $quote->admin_note }}</textarea>
                            <button type="submit" class="btn btn--primary">{{ translate('Save_Note') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">{{ translate('status') }}</h5>

                        @php($map = ['nouveau' => 'danger', 'contacted' => 'warning', 'closed' => 'success'])
                        <p>
                            <span class="badge badge-soft-{{ $map[$quote->status] ?? 'secondary' }}">
                                {{ translate($quote->status) }}
                            </span>
                        </p>

                        <form action="{{ route('admin.psf-quote.status', $quote->id) }}" method="POST"
                              class="d-flex gap-2 mb-3">
                            @csrf
                            <select name="status" class="form-control">
                                @foreach (\App\Models\PsfQuoteRequest::STATUSES as $option)
                                    <option value="{{ $option }}" {{ $quote->status === $option ? 'selected' : '' }}>
                                        {{ translate($option) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn--primary text-nowrap">
                                {{ translate('Update') }}
                            </button>
                        </form>

                        <a href="{{ $whatsappForward }}" target="_blank" rel="noopener"
                           class="btn btn-outline-success w-100 mb-2">
                            <i class="fa fa-whatsapp"></i> {{ translate('Forward_on_WhatsApp') }}
                        </a>

                        <form action="{{ route('admin.psf-quote.delete', $quote->id) }}" method="POST"
                              onsubmit="return confirm('{{ translate('are_you_sure_you_want_to_delete_this') }}');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                {{ translate('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
