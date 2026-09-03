<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PsfQuoteRequest;
use App\Services\PsfQuoteRequestService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\RecaptchaService;

/**
 * PSF — public "Demande de devis" form (client brief §16).
 */
class PsfQuoteRequestController extends Controller
{
    public function __construct(
        private readonly PsfQuoteRequest        $quoteRequest,
        private readonly PsfQuoteRequestService $service,
    ) {
    }

    public function index(): View
    {
        return view('web-views.psf.quote-request', [
            'clientTypes' => psfClientTypes(),
            'recaptcha'   => getWebConfig(name: 'recaptcha'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $captcha = RecaptchaService::verificationStatus(
            request: $request,
            session: 'default_captcha_value_quote',
            action: 'quote'
        );
        if ($captcha && !$captcha['status']) {
            Toastr::error($captcha['message']);
            return back()->withInput();
        }

        $allowedTypes = array_column(psfClientTypes(), 'key');

        $request->validate([
            'name'           => 'required|string|max:191',
            'phone'          => 'required|string|max:40',
            'whatsapp'       => 'nullable|string|max:40',
            'email'          => 'nullable|email|max:191',
            'client_type'    => 'nullable|in:' . implode(',', $allowedTypes),
            'product_sought' => 'nullable|string|max:500',
            'quantity'       => 'nullable|string|max:60',
            'message'        => 'nullable|string|max:5000',
            'attachment'     => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:5120',
        ], [
            'name.required'  => translate('name_is_required'),
            'phone.required' => translate('phone_is_required'),
            'email.email'    => translate('Please_enter_a_valid_email_address'),
            'attachment.mimes' => translate('The_file_must_be_an_image') . ' (jpg, png, webp, pdf, doc)',
            'attachment.max'   => translate('Max_5_MB'),
        ]);

        $quote = $this->service->store(request: $request, model: $this->quoteRequest);
        $this->service->notify(quote: $quote);

        Toastr::success(translate('Your_Message_Send_Successfully'));

        return back();
    }
}
