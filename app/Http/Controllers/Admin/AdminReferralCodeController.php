<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminReferralCodeController extends Controller
{
    public function index()
    {
        $codes = ReferralCode::with('owner')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('admin.referral_codes.index', compact('codes'));
    }

    public function create()
    {
        $admins = User::orderBy('username')->get(['id','username']);
        return view('admin.referral_codes.create', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'          => 'required|string|min:4|max:32|unique:referral_codes,code',
            'max_uses'      => 'required|integer|min:1|max:9999',
            'expires_at'    => 'nullable|date',
            'owner_user_id' => 'nullable|exists:users,id',
        ]);

        DB::table('referral_codes')->insert([
            'id'            => (string) Str::uuid(),
            'code'          => strtoupper(trim($request->code)),
            'max_uses'      => (int) $request->max_uses,
            'uses_count'    => 0,
            'is_active'     => DB::raw('true'),
            'expires_at'    => $request->expires_at ?: null,
            'owner_user_id' => $request->owner_user_id ?: null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('admin.admin.referral-codes.index')
            ->with('success', 'Codigo creado correctamente.');
    }

    public function edit(ReferralCode $referralCode)
    {
        $admins = User::orderBy('username')->get(['id','username']);
        return view('admin.referral_codes.edit', compact('referralCode','admins'));
    }

    public function update(Request $request, ReferralCode $referralCode)
    {
        $request->validate([
            'max_uses'      => 'required|integer|min:1|max:9999',
            'expires_at'    => 'nullable|date',
            'owner_user_id' => 'nullable|exists:users,id',
        ]);

        $isActive = $request->has('is_active');

        DB::table('referral_codes')
            ->where('id', $referralCode->id)
            ->update([
                'max_uses'      => (int) $request->max_uses,
                'expires_at'    => $request->expires_at ?: null,
                'owner_user_id' => $request->owner_user_id ?: null,
                'is_active'     => DB::raw($isActive ? 'true' : 'false'),
                'updated_at'    => now(),
            ]);

        return redirect()->route('admin.admin.referral-codes.index')
            ->with('success', 'Codigo actualizado.');
    }

    public function destroy(ReferralCode $referralCode)
    {
        DB::table('referral_codes')->where('id', $referralCode->id)->delete();
        return redirect()->route('admin.admin.referral-codes.index')
            ->with('success', 'Codigo eliminado.');
    }
}

