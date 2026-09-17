<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

/**
 * PSF — files and form parsing for the storefront design settings
 * (Paramètres PSF → design content).
 *
 * Kept out of the controller for the same reason as PsfGalleryService:
 * FileManagerTrait brings its own update()/delete() methods.
 */
class PsfDesignService
{
    use FileManagerTrait;

    public const DIRECTORY = 'psf-design/';

    public const FOOTER_COLUMN_TYPES = ['categories', 'pages', 'site', 'custom'];

    /**
     * Stores the uploaded image and returns its file name, or null if none.
     */
    public function storeImage(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        return $this->upload(self::DIRECTORY, $file->getClientOriginalExtension(), $file);
    }

    /**
     * Stores an uploaded video as-is (no re-encoding) and returns its file name.
     */
    public function storeVideo(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        return $this->fileUpload(self::DIRECTORY, strtolower($file->getClientOriginalExtension()), $file);
    }

    public function removeImage(?string $fileName): void
    {
        if ($fileName) {
            $this->delete(self::DIRECTORY . $fileName);
        }
    }

    /**
     * Footer columns from the form: footer_type[], footer_title[] and
     * footer_links[] (one "Label | URL" per line, only for custom columns).
     *
     * @return array<int, array{title:string,type:string,links:array<int,array{label:string,url:string}>}>
     */
    public function footerColumns(Request $request): array
    {
        $columns = [];
        foreach ((array) $request['footer_type'] as $index => $type) {
            $type = in_array($type, self::FOOTER_COLUMN_TYPES, true) ? $type : 'custom';

            // custom links: one "Label | URL" list per language
            $links = [];
            if ($type === 'custom') {
                foreach (psfLanguages() as $language) {
                    $code = $language['code'];
                    $links[$code] = [];
                    $text = $request['footer_links'][$index][$code] ?? '';
                    foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
                        $parts = array_map('trim', explode('|', $line, 2));
                        // only web links or site paths (no javascript:, data:…)
                        if (count($parts) === 2 && $parts[0] !== ''
                            && (preg_match('#^https?://#i', $parts[1]) || str_starts_with($parts[1], '/'))) {
                            $links[$code][] = ['label' => strip_tags($parts[0]), 'url' => $parts[1]];
                        }
                    }
                }
            }

            $columns[] = [
                'title' => psfTextFromInput($request['footer_title'][$index] ?? ''),
                'type'  => $type,
                'links' => $links,
            ];
        }

        return array_slice($columns, 0, 3);
    }
}
