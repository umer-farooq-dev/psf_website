{{-- PSF · Pixio: Bootstrap 5 version of the classic partial --}}
@if($web_config['popup_banner'])
    <div class="modal fade" id="popup-modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ translate('close') }}"></button>
                </div>
                <div class="modal-body cursor-pointer __p-3px get-view-by-onclick" data-link="{{ $web_config['popup_banner']['url'] }}">
                    <img class="d-block w-100" alt=""
                         src="{{ getStorageImages(path: $web_config['popup_banner']['photo_full_url'], type: 'banner') }}">
                </div>
            </div>
        </div>
    </div>
@endif
