{{-- PSF · product reviews in the template comment style. Also answers the
     "view more" request (WebController::review_list_product). --}}
@foreach ($productReviews as $productReview)
    @php($psfReviewer = $productReview->user)
    <li class="comment psf-review">
        <div class="comment-body">
            <div class="comment-author vcard">
                <img class="avatar" alt="{{ $psfReviewer ? $psfReviewer->f_name : translate('not_exist') }}"
                     src="{{ $psfReviewer ? getStorageImages(path: $psfReviewer->image_full_url, type: 'avatar') : theme_asset(path: 'public/assets/front-end/img/image-place-holder.png') }}">
                <cite class="fn">{{ $psfReviewer ? trim($psfReviewer->f_name . ' ' . $psfReviewer->l_name) : translate('not_exist') }}</cite>
            </div>
            <div class="d-flex align-items-center gap-3 mb-2 psf-review-meta">
                <ul class="dz-rating mb-0">
                    @for ($psfStar = 1; $psfStar <= 5; $psfStar++)
                        <li class="{{ $psfStar <= (int) $productReview->rating ? 'star-fill' : '' }}"><i class="flaticon-star-1"></i></li>
                    @endfor
                </ul>
                @if ($productReview->updated_at)
                    <span class="psf-review-date">{{ $productReview->updated_at->format('d/m/Y') }}</span>
                @endif
            </div>
            <div class="comment-content dz-page-text">
                <p class="text-break">{{ $productReview->comment }}</p>
                @if (!empty($productReview->attachment_full_url))
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach ($productReview->attachment_full_url as $attachment)
                            <img data-link="{{ getStorageImages(path: $attachment, type: 'product') }}"
                                 class="rounded border show-instant-image psf-review-photo"
                                 src="{{ getStorageImages(path: $attachment, type: 'product') }}" alt="{{ translate('product') }}">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @if ($productReview->reply)
            <ol class="children">
                <li class="comment psf-review-reply">
                    <div class="comment-body">
                        <div class="comment-author vcard">
                            <cite class="fn">{{ translate('Reply_by_Seller') }}</cite>
                        </div>
                        <div class="comment-content dz-page-text">
                            {!! $productReview->reply->reply_text !!}
                        </div>
                    </div>
                </li>
            </ol>
        @endif
    </li>
@endforeach
