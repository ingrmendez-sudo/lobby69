<?php
namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;

class NavbarComposer
{
    public function compose(View $view): void
    {
        $np = null;
        if (Auth::check()) {
            $np = Profile::where('user_id', Auth::id())->first();
        }
        $view->with('np', $np);
    }
}
