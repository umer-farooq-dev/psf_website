<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

/**
 * PSF — image handling for "Nos réalisations".
 *
 * File work lives here rather than in the controller so the controller can
 * keep plain `update()` / `delete()` route methods without clashing with
 * FileManagerTrait's own methods of the same name.
 */
class PsfGalleryService
{
    use FileManagerTrait;

    private const DIRECTORY = 'gallery/';

    /**
     * Stores the uploaded image and returns its file name, or null if none.
     */
    public function storeImage(Request $request, string $field = 'image'): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        return $this->upload(self::DIRECTORY, $file->getClientOriginalExtension(), $file);
    }

    public function removeImage(?string $fileName): void
    {
        if ($fileName) {
            $this->delete(self::DIRECTORY . $fileName);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(Request $request): array
    {
        return [
            // the columns keep the default language; every language goes to translations (saveTexts)
            'title'          => $this->defaultText($request['title']),
            'description'    => $this->defaultText($request['description']),
            'image_alt_text' => $this->defaultText($request['image_alt_text']),
            'category'       => $request['category'],
            'location'       => $this->defaultText($request['location']),
            'completed_on'   => $request['completed_on'] ?: null,
            'sort_order'     => (int)($request['sort_order'] ?? 0),
            'status'         => $request->has('status'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title'          => 'required|array',
            'title.' . psfDefaultLanguageCode() => 'required|string|max:191',
            'title.*'        => 'nullable|string|max:191',
            'description'    => 'nullable|array',
            'description.*'  => 'nullable|string|max:5000',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'image_alt_text' => 'nullable|array',
            'image_alt_text.*' => 'nullable|string|max:191',
            'category'       => 'nullable|string|max:100',
            'location'       => 'nullable|array',
            'location.*'     => 'nullable|string|max:191',
            'completed_on'   => 'nullable|date',
            'sort_order'     => 'nullable|integer|min:0|max:9999',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => translate('Title_is_required'),
            'title.' . psfDefaultLanguageCode() . '.required' => translate('Title_is_required'),
            'image.image'    => translate('The_file_must_be_an_image'),
            'image.max'      => translate('Max_5_MB'),
        ];
    }

    private function defaultText(mixed $value): ?string
    {
        $text = psfTextFromInput($value)[psfDefaultLanguageCode()] ?? '';

        return $text !== '' ? $text : null;
    }

    /**
     * Title, description, location and alt text in every site language.
     */
    public function saveTexts(\App\Models\PsfGalleryItem $item, Request $request): void
    {
        $fields = [];
        foreach (['title', 'description', 'location', 'image_alt_text'] as $field) {
            $fields[$field] = psfTextFromInput($request[$field]);
        }
        psfSaveTranslations($item, $fields);
    }
}
