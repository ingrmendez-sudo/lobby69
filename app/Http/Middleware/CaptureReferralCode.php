<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CaptureReferralCode
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->query('ref')) {
            session(['referral_code' => $request->query('ref')]);
        }
        return $next($request);
    }
}