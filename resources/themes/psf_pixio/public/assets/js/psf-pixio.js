/**
 * PSF — glue between the Pixio design and the shop script (custom.js).
 *
 * The shop script owns every cart/wishlist/search request; this file only
 * adapts it to the new markup. No texts, colours or URLs are written here:
 * routes come from the #route-* spans the layout prints.
 */
(function ($) {
    "use strict";

    const token = () => $('meta[name="_token"]').attr('content');
    const navCartUrl = () => $('#route-cart-nav-cart').data('url');

    /* Cart line removal ------------------------------------------------
       The ✕ removes the whole line, whatever the quantity, through the shop's
       own remove route and refresh (cartItemRemoveFunction in custom.js). */
    $(document).on('click', '.psf-cart-remove', function (e) {
        e.preventDefault();
        const segments = window.location.pathname.split('/');
        cartItemRemoveFunction(
            $('#route-cart-remove').data('url'),
            token(),
            $(this).data('cart-id'),
            segments[segments.length - 1]
        );
    });

    /* Keep the side cart open while it refreshes ------------------------
       After any cart change the shop script replaces #cart_items, which also
       holds the side panel. Bootstrap would leave its backdrop behind and the
       page locked. Remember whether the panel was open, then reopen the fresh
       one on the same tab once the new HTML is in. */
    // Tracked from Bootstrap's own events, so a panel opened while a refresh
    // is still on its way is reopened too.
    let cartOpen = false;
    let cartTab = '#shopping-cart-pane';

    $(document).on('show.bs.offcanvas', '#offcanvasRight', function () {
        cartOpen = true;
    });
    $(document).on('hide.bs.offcanvas', '#offcanvasRight', function () {
        cartOpen = false;
    });
    $(document).on('shown.bs.tab', '#offcanvasRight .nav-tabs [data-bs-target]', function () {
        cartTab = this.getAttribute('data-bs-target');
    });

    $(document).ajaxComplete(function (event, xhr, settings) {
        if (!(settings.url && navCartUrl() && settings.url.indexOf(navCartUrl()) === 0)) {
            return;
        }

        document.querySelectorAll('.offcanvas-backdrop').forEach(function (backdrop) {
            if (!document.querySelector('.offcanvas.show, .offcanvas.showing')) {
                backdrop.remove();
            }
        });
        if (!document.querySelector('.offcanvas.show, .modal.show')) {
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        const countMarker = document.querySelector('[data-psf-cart-count]');
        if (countMarker) {
            $('.psf-cart-count').text(countMarker.getAttribute('data-psf-cart-count'));
        }

        const panel = document.getElementById('offcanvasRight');
        if (cartOpen && panel && window.bootstrap && !panel.classList.contains('show')) {
            document.querySelectorAll('.offcanvas-backdrop').forEach(function (backdrop) {
                backdrop.remove();
            });
            const tab = panel.querySelector('.nav-tabs [data-bs-target="' + cartTab + '"]');
            if (tab) {
                bootstrap.Tab.getOrCreateInstance(tab).show();
            }
            panel.classList.add('psf-no-transition');
            bootstrap.Offcanvas.getOrCreateInstance(panel).show();
            setTimeout(function () {
                panel.classList.remove('psf-no-transition');
            }, 50);
        }
    });

    /* Wishlist icon opens the side panel on its tab ---------------------- */
    $(document).on('click', '.wishlist-link [data-bs-target="#offcanvasRight"]', function () {
        const tab = document.querySelector('#offcanvasRight .nav-tabs [data-bs-target="#wishlist-pane"]');
        if (tab && window.bootstrap) {
            bootstrap.Tab.getOrCreateInstance(tab).show();
        }
    });
    $(document).on('click', '.cart-link [data-bs-target="#offcanvasRight"]', function () {
        const tab = document.querySelector('#offcanvasRight .nav-tabs [data-bs-target="#shopping-cart-pane"]');
        if (tab && window.bootstrap) {
            bootstrap.Tab.getOrCreateInstance(tab).show();
        }
    });

    /* Product cards ---------------------------------------------------------
       Delegated, so cards cloned by sliders (loop mode) work too. */
    // on a product page the page itself holds the cart form a quick view
    // would duplicate, so other products open on their own page there
    function openProductPageInstead($button) {
        if (document.querySelector('.psf-product-page') && $button.data('url')) {
            window.location.href = $button.data('url');
            return true;
        }
        return false;
    }

    $(document).on('click', '.psf-quick-view', function (e) {
        e.preventDefault();
        if (openProductPageInstead($(this))) {
            return;
        }
        if (typeof productQuickView === 'function') {
            productQuickView($(this).data('product-id'));
        }
    });

    $(document).on('click', '.psf-wishlist', function (e) {
        e.preventDefault();
        if (typeof addWishlist === 'function') {
            addWishlist($(this).data('product-id'));
        }
    });

    // the heart follows the shop's answer: 1 = added, 2 = removed
    $(document).ajaxSuccess(function (event, xhr, settings, data) {
        const wishlistUrl = $('#route-store-wishlist').data('url');
        if (!wishlistUrl || !settings.url || settings.url.indexOf(wishlistUrl) !== 0 || !data) {
            return;
        }
        const match = /product_id=(\d+)/.exec(String(settings.data || ''));
        if (match && (data.value == 1 || data.value == 2)) {
            $('.psf-wishlist[data-product-id="' + match[1] + '"]').toggleClass('active', data.value == 1);
        }
    });

    $(document).on('click', '.psf-add-cart', function (e) {
        e.preventDefault();
        const $button = $(this);
        const productId = $button.data('product-id');

        // a colour or a variation has to be chosen first
        if (String($button.data('options')) === '1') {
            if (openProductPageInstead($button)) {
                return;
            }
            if (typeof productQuickView === 'function') {
                productQuickView(productId);
            }
            return;
        }
        if ($button.hasClass('psf-busy')) {
            return;
        }

        $button.addClass('psf-busy');
        $.post({
            url: $('#route-cart-add').data('url'),
            data: {_token: token(), id: productId, quantity: $button.data('min') || 1},
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (response) {
                if (response.status == 1) {
                    $('.psf-add-cart[data-product-id="' + productId + '"]').addClass('active');
                    if (typeof updateNavCart === 'function') {
                        updateNavCart();
                    }
                    toastr.success(response.message);
                } else if (response.message) {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message;
                if (message) {
                    toastr.error(message);
                }
            },
            complete: function () {
                $button.removeClass('psf-busy');
                $('#loading').hide();
            }
        });
    });

    /* Countdowns (flash deal in the shop menu, deal blocks) ----------------
       The end date is printed by the server from the deal saved in the panel. */
    $(function () {
        $('.psf-countdown[data-end]').each(function () {
            const $box = $(this);
            if (typeof $box.countdown === 'function') {
                $box.countdown({date: $box.data('end')}, function () {
                    $box.closest('.month-deal').hide();
                });
            }
        });
    });

    /* Announcement bar ---------------------------------------------------- */
    $(document).on('click', '.psf-announcement-close', function () {
        $(this).closest('.psf-announcement').slideUp(200);
    });

})(jQuery);
