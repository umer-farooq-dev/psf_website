<?php

namespace App\Services;

use App\Models\PsfQuoteRequest;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * PSF — storing and announcing a "Demande de devis".
 */
class PsfQuoteRequestService
{
    use FileManagerTrait;

    public function store(object $request, PsfQuoteRequest $model): PsfQuoteRequest
    {
        $storage = config('filesystems.disks.default') ?? 'public';

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $extension = strtolower($file->getClientOriginalExtension());
            $attachment = $this->upload('quote-requests/', $extension, $file);
        }

        return $model->create([
            'name'                    => $request['name'],
            'phone'                   => $request['phone'],
            'whatsapp'                => $request['whatsapp'] ?? null,
            'email'                   => $request['email'] ?? null,
            'client_type'             => $request['client_type'] ?? null,
            'product_sought'          => $request['product_sought'] ?? null,
            'quantity'                => $request['quantity'] ?? null,
            'message'                 => $request['message'] ?? null,
            'attachment'              => $attachment,
            'attachment_storage_type' => $attachment ? $storage : 'public',
            'status'                  => 'nouveau',
            'seen'                    => false,
            'ip'                      => $request->ip(),
        ]);
    }

    /**
     * Announce the request to PSF.
     *
     * Never lets a mail problem break the customer's submission — the
     * request is already saved by the time we get here.
     */
    public function notify(PsfQuoteRequest $quote): void
    {
        $recipients = psfQuoteRecipients();

        if (!empty($recipients['email'])) {
            try {
                Mail::raw($this->plainSummary($quote), function ($message) use ($recipients, $quote) {
                    $message->to($recipients['email'])
                        ->subject(translate('New_quote_request') . ' #' . $quote->id . ' — ' . $quote->name);
                });
            } catch (\Throwable $e) {
                Log::warning('PSF quote request mail failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * A ready-to-send text summary, also used for the WhatsApp forward link.
     */
    public function plainSummary(PsfQuoteRequest $quote): string
    {
        $lines = [
            translate('New_quote_request') . ' #' . $quote->id,
            '',
            translate('Name') . ': ' . $quote->name,
            translate('Phone') . ': ' . $quote->phone,
        ];

        if ($quote->whatsapp) {
            $lines[] = 'WhatsApp: ' . $quote->whatsapp;
        }
        if ($quote->email) {
            $lines[] = translate('Email') . ': ' . $quote->email;
        }
        if ($quote->client_type) {
            $lines[] = translate('Client_Type') . ': ' . $quote->client_type_label;
        }
        if ($quote->product_sought) {
            $lines[] = translate('Product_Sought') . ': ' . $quote->product_sought;
        }
        if ($quote->quantity) {
            $lines[] = translate('Quantity') . ': ' . $quote->quantity;
        }
        if ($quote->message) {
            $lines[] = '';
            $lines[] = translate('Message') . ': ' . $quote->message;
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * wa.me link so PSF staff can forward a request into WhatsApp in one click.
     */
    public function whatsappForwardUrl(PsfQuoteRequest $quote): string
    {
        $recipients = psfQuoteRecipients();

        return 'https://wa.me/' . $recipients['whatsapp'] . '?text=' . rawurlencode($this->plainSummary($quote));
    }
}
