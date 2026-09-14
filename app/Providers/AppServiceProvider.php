<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
        // Blade directive para wrappear imágenes protegidas con watermark
        \Illuminate\Support\Facades\Blade::directive('protectedImg', function ($expression) {
            return "<?php
                \$_l69Parts = array_map('trim', explode(',', $expression));
                \$_l69Src   = \$_l69Parts[0] ?? '';
                \$_l69Alt   = \$_l69Parts[1] ?? \"'foto'\";
                \$_l69User  = auth()->user()->username ?? '';
                echo '<div class=\"l69-media-wrap\">'
                   . '<img src=\"' . e(trim(\$_l69Src, \"'\\\"\")) . '\" '
                   . 'alt=\"' . e(trim(\$_l69Alt, \"'\\\"\")) . '\" '
                   . 'draggable=\"false\" oncontextmenu=\"return false\" loading=\"lazy\">'
                   . '<div class=\"l69-watermark\">@lobby69 • ' . e(\$_l69User) . '</div>'
                   . '</div>';
            ?>";
        });
    {
        View::composer('layouts.admin', AdminPendingComposer::class);
        View::composer('components.navbar', NavbarComposer::class);
        View::composer('layouts.sidebar-left', SidebarComposer::class);
    }
}
