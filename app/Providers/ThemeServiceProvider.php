<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        if (!App::runningInConsole()) {
            $theme = env('WEB_THEME') == null ? 'default' : env('WEB_THEME');
            $path = base_path('resources/themes/' . $theme);

            // PSF: the redesigned storefront is a layer over the default theme,
            // not a theme of its own. theme_root_path() keeps returning
            // "default", so every controller branch written for that theme
            // (login, addresses, search, home data…) behaves exactly as before;
            // only the views come from psf_pixio, with default as the fallback
            // for anything the new design does not override.
            // Switched from Paramètres PSF → Design (PSF_FRONTEND_DESIGN).
            $psfDesignPath = base_path('resources/themes/psf_pixio');
            $usePsfDesign = $theme === 'default'
                && env('PSF_FRONTEND_DESIGN') === 'pixio'
                && is_file($psfDesignPath . '/file_names.php');

            if (!defined('VIEW_FILE_NAMES')) {
                define("VIEW_FILE_NAMES", include(($usePsfDesign ? $psfDesignPath : $path) . '/file_names.php'));
            }
            if ($usePsfDesign) {
                view()->addLocation($psfDesignPath);
            }
            view()->addLocation($path);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
