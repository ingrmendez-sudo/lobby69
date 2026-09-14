<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\View\Composers\AdminPendingComposer;
use App\View\Composers\NavbarComposer;
use App\View\Composers\SidebarComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // View Composers
        View::composer('layouts.admin', AdminPendingComposer::class);
        View::composer('components.navbar', NavbarComposer::class);
        View::composer('layouts.sidebar-left', SidebarComposer::class);

        // Blade directive: @protectedImg($src, $alt)
        Blade::directive('protectedImg', function ($expression) {
            return <<<'PHPCODE'
<?php
    $_l69Parts = array_map('trim', explode(',', (string)($expression)));
    $_l69Src   = trim($_l69Parts[0] ?? '', "\"'");
    $_l69Alt   = trim($_l69Parts[1] ?? 'foto', "\"'");
    $_l69User  = auth()->check() ? auth()->user()->username : '';
    echo '<div class="l69-media-wrap">'
       . '<img src="' . e($_l69Src) . '" '
       . 'alt="' . e($_l69Alt) . '" '
       . 'draggable="false" oncontextmenu="return false" loading="lazy">'
       . '<div class="l69-watermark">@lobby69 &bull; ' . e($_l69User) . '</div>'
       . '</div>';
?>
PHPCODE;
        });
    }
}
