<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\PsfQuoteRequest;
use App\Services\PsfQuoteRequestService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * PSF — admin inbox for "Demandes de devis" (client brief §16).
 */
class PsfQuoteRequestController extends BaseController
{
    public function __construct(
        private readonly PsfQuoteRequest        $quoteRequest,
        private readonly PsfQuoteRequestService $service,
    ) {
    }

    public function index(?Request $request, string $type = null): View
    {
        $search = $request['searchValue'] ?? null;
        $status = $request['status'] ?? null;

        $quotes = $this->quoteRequest->query()
            ->status($status)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('product_sought', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(getWebConfig(name: 'pagination_limit') ?: 25)
            ->appends(['searchValue' => $search, 'status' => $status]);

        return view('admin-views.psf.quote-request.list', [
            'quotes'      => $quotes,
            'search'      => $search,
            'status'      => $status,
            'statusCount' => [
                'all'       => $this->quoteRequest->count(),
                'nouveau'   => $this->quoteRequest->where('status', 'nouveau')->count(),
                'contacted' => $this->quoteRequest->where('status', 'contacted')->count(),
                'closed'    => $this->quoteRequest->where('status', 'closed')->count(),
            ],
        ]);
    }

    public function view(string|int $id): View|RedirectResponse
    {
        $quote = $this->quoteRequest->find($id);
        if (!$quote) {
            Toastr::error(translate('No_quote_request_found'));
            return redirect()->route('admin.psf-quote.index');
        }

        if (!$quote->seen) {
            $quote->seen = true;
            $quote->save();
        }

        return view('admin-views.psf.quote-request.view', [
            'quote'            => $quote,
            'whatsappForward'  => $this->service->whatsappForwardUrl($quote),
        ]);
    }

    public function updateStatus(Request $request, string|int $id): RedirectResponse
    {
        $request->validate(['status' => 'required|in:' . implode(',', PsfQuoteRequest::STATUSES)]);

        $quote = $this->quoteRequest->find($id);
        if (!$quote) {
            Toastr::error(translate('No_quote_request_found'));
            return back();
        }

        $quote->status = $request['status'];
        $quote->save();

        Toastr::success(translate('status_updated_successfully'));
        return back();
    }

    public function updateNote(Request $request, string|int $id): RedirectResponse
    {
        $request->validate(['admin_note' => 'nullable|string|max:5000']);

        $quote = $this->quoteRequest->find($id);
        if (!$quote) {
            Toastr::error(translate('No_quote_request_found'));
            return back();
        }

        $quote->admin_note = $request['admin_note'];
        $quote->save();

        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function delete(string|int $id): RedirectResponse
    {
        $quote = $this->quoteRequest->find($id);
        if ($quote) {
            $quote->delete();
            Toastr::success(translate('deleted_successfully'));
        }

        return redirect()->route('admin.psf-quote.index');
    }

    /**
     * CSV export — opens directly in Excel, no extra package needed.
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request['status'] ?? null;
        $quotes = $this->quoteRequest->query()->status($status)->latest()->get();

        $filename = 'demandes-de-devis-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($quotes) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads the accents
            fputcsv($out, [
                'ID', translate('Name'), translate('Phone'), 'WhatsApp', translate('Email'),
                translate('Client_Type'), translate('Product_Sought'), translate('Quantity'),
                translate('Message'), translate('status'), translate('Submitted_on'),
            ], ';');

            foreach ($quotes as $quote) {
                fputcsv($out, [
                    $quote->id, $quote->name, $quote->phone, $quote->whatsapp, $quote->email,
                    $quote->client_type_label, $quote->product_sought, $quote->quantity,
                    $quote->message, translate($quote->status),
                    $quote->created_at?->format('Y-m-d H:i'),
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
