# PSF — Plomberie Sanitaire du Faso

Catalogue and quotation website for Plomberie Sanitaire du Faso, a plumbing and
sanitary equipment supplier in Ouagadougou, Burkina Faso.


## What is specific to PSF

Everything PSF-specific is prefixed `psf` and is configured from the panel — no
values are fixed in the code.

| Area | Where |
|---|---|
| Helpers (menus, price rule, contact, homepage, SEO, brand) | `app/Utils/psf-menu.php` |
| Quote requests (*Demandes de devis*) | `app/Models/PsfQuoteRequest.php`, `app/Services/PsfQuoteRequestService.php` |
| Gallery (*Nos réalisations*) | `app/Models/PsfGalleryItem.php`, `app/Services/PsfGalleryService.php` |
| One settings page for all PSF options | `app/Http/Controllers/Admin/PsfSettingsController.php` |
| Public views | `resources/themes/default/web-views/psf/`, `.../partials/_psf-*.blade.php` |
| Admin views | `resources/views/admin-views/psf/` |

All options live in the `business_settings` table under `psf_*` keys and are edited
from **Paramètres PSF** in the admin panel.

### The price rule

A product with a price behaves normally (cart, checkout). A product with a blank or
zero price shows *« Contactez-nous pour le prix »* and offers a WhatsApp button
instead of the cart. This is enforced in the price helpers and in
`CartManager::add_to_cart()`, so it holds even for requests that skip the UI.

## Local setup

```bash
composer install
cp .env.example .env      # then fill in database credentials
php artisan key:generate
php artisan migrate
php artisan storage:link
```

## Before going live

- Set `APP_DEBUG=false` and point `APP_URL` at the real domain
- Regenerate `robots.txt` and the sitemap from the admin SEO settings
- Confirm phone numbers, address and opening hours with PSF
- Change the administrator password

Never commit `.env`, `.env.backup*`, or the `storage/oauth-*.key` files.
