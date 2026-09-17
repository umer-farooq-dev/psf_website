{{-- PSF · one text in every active site language.
     $name        : field name, e.g. "slogan" or "cta_label[0]" → slogan[fr], slogan[en]
     $values      : [code => text] (psfTextArray)
     $placeholder : optional
     $textarea    : optional, rows count for a textarea --}}
<div class="d-flex flex-column gap-1 psf-lang-input">
    @foreach (psfLanguages() as $psfLanguage)
        <div class="input-group">
            <span class="input-group-text text-uppercase fs-12 fw-semibold psf-lang-code" title="{{ $psfLanguage['name'] }}">{{ $psfLanguage['code'] }}</span>
            @if (!empty($textarea))
                <textarea class="form-control" rows="{{ $textarea }}" name="{{ $name }}[{{ $psfLanguage['code'] }}]"
                          placeholder="{{ $placeholder ?? '' }}"
                          aria-label="{{ $psfLanguage['name'] }}">{{ $values[$psfLanguage['code']] ?? '' }}</textarea>
            @else
                <input type="text" class="form-control" name="{{ $name }}[{{ $psfLanguage['code'] }}]"
                       value="{{ $values[$psfLanguage['code']] ?? '' }}" placeholder="{{ $placeholder ?? '' }}"
                       aria-label="{{ $psfLanguage['name'] }}">
            @endif
        </div>
    @endforeach
</div>
