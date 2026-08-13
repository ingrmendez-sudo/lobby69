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
        View::composer('layouts.admin', AdminPendingComposer::class);
        View::composer('components.navbar', NavbarComposer::class);
        View::composer('layouts.sidebar-left', SidebarComposer::class);
    }
}