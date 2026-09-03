<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * PSF — "Demande de devis" (client brief §16).
 */
class PsfQuoteRequest extends Model
{
    protected $table = 'psf_quote_requests';

    protected $fillable = [
        'name',
        'phone',
        'whatsapp',
        'email',
        'client_type',
        'product_sought',
        'quantity',
        'message',
        'attachment',
        'attachment_storage_type',
        'status',
        'seen',
        'admin_note',
        'ip',
    ];

    protected $casts = [
        'seen' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUSES = ['nouveau', 'contacted', 'closed'];

    public function scopeUnseen(Builder $query): Builder
    {
        return $query->where('seen', false);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Label of the selected client type, taken from the admin-managed list.
     */
    public function getClientTypeLabelAttribute(): string
    {
        foreach (psfClientTypes() as $type) {
            if (($type['key'] ?? null) === $this->client_type) {
                return $type['label'] ?? $this->client_type;
            }
        }

        return (string)$this->client_type;
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (empty($this->attachment)) {
            return null;
        }

        return asset('storage/app/public/quote-requests/' . $this->attachment);
    }
}
