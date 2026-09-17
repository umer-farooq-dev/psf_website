/*
 * PSF · shop pages of the new design (products, category, brand, offers…).
 *
 * The page address holds the filters (?product_name=…&min_price=…&color_ids[]=…).
 * Every control changes that address and asks the same page for the new
 * list; the shop answers with html_products (ProductListController), which
 * replaces the list. Back / forward buttons replay the address.
 */
(function ($) {
    'use strict';

    const $shop = $('#psf-shop');
    if (!$shop.length) {
        return;
    }

    const baseUrl = String($shop.data('url'));
    let params = new URLSearchParams(window.location.search);
    let pending = null;

    /* ---------- list request ---------- */

    function load(options) {
        const settings = $.extend({resetPage: true, push: true, scroll: false}, options);
        if (settings.resetPage) {
            params.delete('page');
        }

        const query = params.toString();
        const address = baseUrl + (query ? '?' + query : '');
        if (settings.push) {
            window.history.pushState({psfShop: true}, '', address);
        }

        if (pending) {
            pending.abort();
        }
        $shop.addClass('psf-loading');
        pending = $.ajax({
            url: address,
            method: 'GET',
            dataType: 'json',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
        }).done(function (response) {
            if (response && typeof response.html_products === 'string') {
                $('#ajax-products-view').html(response.html_products);
                showSummary();
                if (settings.scroll) {
                    const top = $shop.offset().top - 120;
                    window.scrollTo({top: top > 0 ? top : 0, behavior: 'smooth'});
                }
                if (typeof WOW === 'function') {
                    new WOW().init();
                }
            } else if (response && response.message && typeof toastr !== 'undefined') {
                toastr.error(response.message);
            }
        }).fail(function (xhr, status) {
            if (status !== 'abort') {
                window.location.href = address;
            }
        }).always(function () {
            pending = null;
            $shop.removeClass('psf-loading');
        });
    }

    /* the list carries its count and active-filter tags in a <template> */
    function showSummary() {
        const template = $('#ajax-products-view template.psf-shop-summary').get(0);
        if (!template) {
            return;
        }
        const $content = $(template.content.cloneNode(true));
        $('.psf-shop-summary-target .filter-tag').replaceWith($content.find('.filter-tag'));
        $('.psf-shop-summary-target .psf-shop-count').replaceWith($content.find('.psf-shop-count'));
    }

    function setParam(name, value) {
        if (value === null || value === undefined || String(value).trim() === '') {
            params.delete(name);
        } else {
            params.set(name, String(value).trim());
        }
    }

    /* ---------- toolbar ---------- */

    $shop.on('change', '.psf-shop-select', function () {
        setParam(this.name, $(this).val());
        load();
    });

    $shop.on('click', '.psf-shop-view', function (e) {
        e.preventDefault();
        const view = $(this).data('view');
        $shop.find('.psf-shop-view').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        setParam('view', view);
        load({resetPage: false});
    });

    $shop.on('click', '.psf-pagination a.page-link[data-page]', function (e) {
        e.preventDefault();
        setParam('page', $(this).data('page'));
        load({resetPage: false, scroll: true});
    });

    $shop.on('click', '.psf-filter-remove', function (e) {
        const clear = $(this).data('clear');
        const clearValue = $(this).data('clear-value');
        if (!clear && !clearValue) {
            return; // plain link (category / brand page)
        }
        e.preventDefault();
        if (clear) {
            String(clear).split(',').forEach(function (name) {
                params.delete(name);
            });
            if (String(clear).indexOf('product_name') !== -1 && params.get('data_from') === 'search') {
                params.delete('data_from');
            }
        }
        if (clearValue) {
            const parts = String(clearValue).split('|');
            const kept = params.getAll(parts[0]).filter(function (value) {
                return value !== parts.slice(1).join('|');
            });
            params.delete(parts[0]);
            kept.forEach(function (value) {
                params.append(parts[0], value);
            });
        }
        syncControls();
        load();
    });

    /* ---------- sidebar ---------- */

    $shop.on('submit', '.psf-shop-search', function (e) {
        e.preventDefault();
        const term = $(this).find('input[name="product_name"]').val();
        params.delete('name');
        params.delete('search');
        if (params.get('data_from') === 'search') {
            params.delete('data_from');
        }
        setParam('product_name', term);
        load();
    });

    $shop.on('search', '.psf-shop-search input[type="search"]', function () {
        if ($(this).val() === '' && (params.has('product_name') || params.has('name') || params.has('search'))) {
            $(this).closest('form').trigger('submit');
        }
    });

    $shop.on('change', '.psf-filter-color', function () {
        params.delete('color_ids[]');
        $shop.find('.psf-filter-color:checked').each(function () {
            params.append('color_ids[]', this.value);
        });
        load();
    });

    // a picked value can be picked again to take it off
    $shop.on('mousedown keydown', '.psf-size-pills label.btn', function () {
        const $input = $('#' + $(this).attr('for'));
        $input.data('wasChecked', $input.prop('checked'));
    });
    $shop.on('click', '.psf-filter-attribute', function () {
        if ($(this).data('wasChecked')) {
            $(this).prop('checked', false);
        }
        $(this).data('wasChecked', false);
        setParam(this.name, this.checked ? this.value : '');
        load();
    });

    $shop.on('click', '.psf-filter-link', function (e) {
        e.preventDefault();
        const name = $(this).data('name');
        let value = String($(this).data('value'));
        if ($(this).data('toggle') && params.get(name) === value) {
            value = '';
        }
        if (name === 'product_type' && value === 'all') {
            value = '';
        }
        setParam(name, value);
        syncControls();
        load();
    });

    /* ---------- price slider ---------- */

    const slider = document.getElementById('psf-price-slider');
    const $slider = $(slider);

    function money(value) {
        const amount = Math.round(Number(value)).toLocaleString();
        const symbol = String($slider.data('symbol') || '');
        return $slider.data('position') === 'left' ? symbol + amount : amount + symbol;
    }

    if (slider && typeof noUiSlider !== 'undefined') {
        const min = Number($slider.data('min')) || 0;
        const max = Number($slider.data('max')) || 0;
        if (max > min) {
            noUiSlider.create(slider, {
                start: [Number($slider.data('from')) || min, Number($slider.data('to')) || max],
                connect: true,
                step: 1,
                range: {min: min, max: max},
            });
            const $min = $('#psf-price-min');
            const $max = $('#psf-price-max');
            slider.noUiSlider.on('update', function (values) {
                $min.text($min.data('label') + ' : ' + money(values[0]));
                $max.text($max.data('label') + ' : ' + money(values[1]));
            });
            slider.noUiSlider.on('change', function (values) {
                const from = Math.round(Number(values[0]));
                const to = Math.round(Number(values[1]));
                setParam('min_price', from > min ? from : '');
                setParam('max_price', to < max ? to : '');
                load();
            });
        }
    }

    /* keep the sidebar in step with the address (tag removed, back button…) */
    function syncControls() {
        const colors = params.getAll('color_ids[]');
        $shop.find('.psf-filter-color').each(function () {
            this.checked = colors.indexOf(this.value) !== -1;
        });
        $shop.find('.psf-filter-attribute').each(function () {
            this.checked = params.get(this.name) === this.value;
        });
        $shop.find('.psf-filter-link').each(function () {
            const name = $(this).data('name');
            const value = String($(this).data('value'));
            const current = params.get(name) || (name === 'product_type' ? 'all' : '');
            const active = current === value;
            $(this).toggleClass('active', active).closest('.cat-item').toggleClass('current-cat', active);
        });
        $shop.find('.psf-shop-search input[name="product_name"]').val(params.get('product_name') || params.get('name') || params.get('search') || '');
        if (slider && slider.noUiSlider) {
            slider.noUiSlider.set([
                params.get('min_price') || $slider.data('min'),
                params.get('max_price') || $slider.data('max'),
            ]);
        }
        $shop.find('select.psf-shop-select').each(function () {
            const value = params.get(this.name);
            if (value !== null && $(this).val() !== value) {
                $(this).val(value);
                if ($.fn.selectpicker) {
                    $(this).selectpicker('refresh');
                }
            }
        });
    }

    window.addEventListener('popstate', function () {
        params = new URLSearchParams(window.location.search);
        const view = params.get('view');
        if (view) {
            $shop.find('.psf-shop-view').removeClass('active').filter('[data-view="' + view + '"]').addClass('active');
        }
        syncControls();
        load({resetPage: false, push: false});
    });

    showSummary();
})(jQuery);
