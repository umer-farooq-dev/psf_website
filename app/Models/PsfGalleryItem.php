<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * PSF — one entry of "Nos réalisations" (client brief §17).
 */
class PsfGalleryItem extends Model
{
    protected $table = 'psf_gallery_items';

    protected $fillable = [
        'title',
        'description',
        'image',
        'image_alt_text',
        'image_storage_type',
        'category',
        'location',
        'completed_on',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status'       => 'boolean',
        'sort_order'   => 'integer',
        'completed_on' => 'date',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when($category, fn ($q) => $q->where('category', $category));
    }

    /**
     * Newest finished project first, but a hand-set order always wins.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('completed_on')->orderByDesc('id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return url('storage/app/public/gallery/' . $this->image);
    }

    /**
     * Falls back to the title so an image is never announced as unlabelled.
     */
    public function getAltAttribute(): string
    {
        return $this->text('image_alt_text') ?: $this->text('title');
    }

    /**
     * A text field in the visitor's language (falls back to the default one).
     */
    public function text(string $field): string
    {
        return psfRecordText($this, $field);
    }
}
