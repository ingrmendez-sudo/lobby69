<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        return view('dashboard.index', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }
}
