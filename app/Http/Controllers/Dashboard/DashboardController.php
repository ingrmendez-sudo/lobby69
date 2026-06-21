<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Account;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var Account $account */
        $account = auth()->user();

        return view('dashboard.index', [
            'account' => $account,
            'profile' => $account->profile,
        ]);
    }
}
