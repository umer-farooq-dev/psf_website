{{-- PSF · Pixio footer. Contact details, hours and slogan come from Paramètres PSF,
     the three link columns from Paramètres PSF → Design, logo and copyright from
     Business setup, the "recent" column from Nos réalisations. --}}
@php($psfPhones = psfContactPhones())
@php($psfAddress = psfContactAddress())
@php($psfEmail = $web_config['email'] ?? getWebConfig(name: 'company_email'))
@php($psfHoursLine = psfOpeningHoursSummary())
@php($psfRecent = \App\Models\PsfGalleryItem::active()->ordered()->take(3)->get())
@php($psfPaymentImage = psfFooterPaymentImage())

<footer class="site-footer bg-light">
    <div class="footer-top">
        <div class="container">
            <div class="row">

                <div class="col-xl-3 col-md-4 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="widget widget_about me-2">
                        <div class="footer-logo logo-dark">
                            <a href="{{ route('home') }}">
                                <img src="{{ getStorageImages(path: $web_config['footer_logo'], type: 'logo') }}" alt="{{ $web_config['company_name'] }}">
                            </a>
                        </div>
                        @if (psfSlogan())
                            <p class="psf-footer-slogan">{{ psfSlogan() }}</p>
                        @endif
                        <ul class="widget-address">
                            @if ($psfAddress)
                                <li><p><span>{{ translate('address') }}</span> : {{ $psfAddress }}</p></li>
                            @endif
                            @if ($psfEmail)
                                <li><p><span>{{ translate('email') }}</span> : <a href="mailto:{{ $psfEmail }}">{{ $psfEmail }}</a></p></li>
                            @endif
                            @foreach ($psfPhones as $psfPhone)
                                <li>
                                    <p>
                                        <span>{{ $psfPhone['label'] ?: translate('phone') }}</span> :
                                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $psfPhone['number']) }}">{{ $psfPhone['number'] }}</a>
                                    </p>
                                </li>
                            @endforeach
                            @if ($psfHoursLine !== '')
                                <li><p><span>{{ translate('Opening_Hours') }}</span> : {{ $psfHoursLine }}</p></li>
                            @endif
                        </ul>
                        <div class="subscribe_widget">
                            <h6 class="title fw-medium">{{ translate('subscribe_to_our_newsletter') }}</h6>
                            <form class="dzSubscribe style-1" action="{{ route('subscription') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <div class="input-group mb-0">
                                        <input name="subscription_email" required type="email" class="form-control"
                                               placeholder="{{ translate('your_Email_Address') }}" aria-label="{{ translate('your_Email_Address') }}">
                                        <div class="input-group-addon">
                                            <button type="submit" class="btn" aria-label="{{ translate('subscribe') }}">
                                                <i class="icon feather icon-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="widget widget_post">
                        <h5 class="footer-title">{{ translate('Our_Realisations') }}</h5>
                        @if ($psfRecent->count() > 0)
                            <ul>
                                @foreach ($psfRecent as $psfWork)
                                    <li>
                                        <div class="dz-media">
                                            @if ($psfWork->image_url)
                                                <img src="{{ $psfWork->image_url }}" alt="{{ $psfWork->alt }}" loading="lazy">
                                            @endif
                                        </div>
                                        <div class="dz-content">
                                            <h6 class="name"><a href="{{ route('psf.gallery.index') }}">{{ $psfWork->text('title') }}</a></h6>
                                            @if ($psfWork->completed_on)
                                                <span class="time">{{ $psfWork->completed_on->translatedFormat('d F Y') }}</span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mb-0">{{ translate('No_realisation_found') }}</p>
                        @endif
                    </div>
                </div>

                @foreach (psfFooterColumns() as $psfIndex => $psfColumn)
                    @if (count($psfColumn['links']) > 0)
                        <div class="col-xl-2 col-md-{{ $psfIndex === 2 ? '3' : '3' }} col-sm-4 {{ $psfIndex < 2 ? 'col-6' : '' }} wow fadeInUp"
                             data-wow-delay="{{ 0.3 + $psfIndex * 0.1 }}s">
                            <div class="widget widget_services">
                                <h5 class="footer-title">{{ $psfColumn['title'] }}</h5>
                                <ul>
                                    @foreach ($psfColumn['links'] as $psfLink)
                                        <li><a href="{{ $psfLink['url'] }}">{{ $psfLink['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endforeach

            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="row fb-inner wow fadeInUp" data-wow-delay="0.1s">
                <div class="col-lg-6 col-md-12 text-start">
                    <p class="copyright-text">{{ $web_config['copyright_text'] }}</p>
                </div>
                <div class="col-lg-6 col-md-12 text-end">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-center justify-content-xl-end gap-3 flex-wrap">
                        @if (!empty($web_config['social_media']) && count($web_config['social_media']) > 0)
                            <div class="dz-social-icon psf-footer-social">
                                <ul>
                                    @foreach ($web_config['social_media'] as $psfSocial)
                                        <li>
                                            <a class="{{ psfSocialIconClass($psfSocial->name) }}" target="_blank" rel="noopener"
                                               href="{{ $psfSocial->link }}" aria-label="{{ $psfSocial->name }}"></a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($psfPaymentImage)
                            <span>{{ translate('we_accept') }} :</span>
                            <img src="{{ $psfPaymentImage }}" alt="{{ translate('we_accept') }}" class="psf-payment-image">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
