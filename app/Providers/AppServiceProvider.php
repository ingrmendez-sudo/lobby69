<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\AdminPendingComposer;
use App\View\Composers\NavbarComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Inyecta contadores de pendientes en el layout admin
        View::composer('layouts.admin', AdminPendingComposer::class);
        View::composer('components.navbar', NavbarComposer::class);
    }
}

