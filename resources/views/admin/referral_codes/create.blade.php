@extends('layouts.admin')

@section('title', 'Nuevo Codigo de Referido')
@section('page-title', 'Nuevo Codigo de Referido')

@section('content')
<div style="max-width:640px;">

    <div class="mb-4">
        <a href="{{ route('admin.referral-codes.index') }}"
           style="color:var(--theme-muted);font-size:.875rem;text-decoration:none;">
            &larr; Volver a codigos
        </a>
        <h4 class="mt-2 mb-0" style="color:var(--theme-text);font-weight:600;">Nuevo codigo de referido</h4>
    </div>

    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:12px;padding:2rem;">
        <form method="POST" action="{{ route('admin.referral-codes.store') }}">
            @csrf

            {{-- Codigo --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Codigo de referido
                    <small style="color:var(--theme-muted);font-weight:400;margin-left:.25rem;">(se guardara en mayusculas)</small>
                </label>
                <div class="input-group">
                    <input type="text" name="code" id="inputCode"
                           class="form-control form-control-lg @error('code') is-invalid @enderror"
                           value="{{ old('code') }}"
                           placeholder="LOBBY-ABC123"
                           style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);font-family:monospace;letter-spacing:.05em;font-size:1rem;">
                    <button type="button" id="btnGenerar" class="btn btn-outline-secondary px-3"
                            style="border-color:var(--theme-border);color:var(--theme-muted);">
                        ⚡ Generar
                    </button>
                </div>
                @error('code')
                    <div class="invalid-feedback d-block" style="color:#e74c3c;font-size:.825rem;margin-top:.35rem;">{{ $message }}</div>
                @enderror
                {{-- Preview link --}}
                <div style="margin-top:.75rem;padding:.65rem .9rem;background:rgba(212,175,55,.07);border-radius:6px;border:1px dashed rgba(212,175,55,.3);">
                    <small style="color:var(--theme-muted);display:block;margin-bottom:.2rem;font-weight:600;">Link de invitacion:</small>
                    <code id="previewLink" style="color:#d4af37;font-size:.825rem;word-break:break-all;">
                        {{ url('/invitacion') }}?ref=<span id="previewCode">LOBBY-ABC123</span>
                    </code>
                </div>
            </div>

            {{-- Maximo usos --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">Maximo de usos</label>
                <input type="number" name="max_uses" min="1" max="9999"
                       class="form-control @error('max_uses') is-invalid @enderror"
                       value="{{ old('max_uses', 10) }}"
                       style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);">
                @error('max_uses')
                    <div style="color:#e74c3c;font-size:.825rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
                <small style="color:var(--theme-muted);display:block;margin-top:.3rem;">Cuantas personas pueden registrarse con este codigo.</small>
            </div>

            {{-- Expiracion --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Fecha de expiracion
                    <small style="color:var(--theme-muted);font-weight:400;margin-left:.25rem;">(opcional)</small>
                </label>
                <input type="date" name="expires_at"
                       class="form-control"
                       value="{{ old('expires_at') }}"
                       style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);">
            </div>

            {{-- Propietario --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Asignar a usuario
                    <small style="color:var(--theme-muted);font-weight:400;margin-left:.25rem;">(opcional)</small>
                </label>
                <select name="owner_user_id" class="form-select"
                        style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);">
                    <option value="">— Sistema (sin propietario) —</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ old('owner_user_id') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->username }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Botones --}}
            <div class="d-flex gap-3" style="padding-top:1.25rem;border-top:1px solid var(--theme-border);">
                <button type="submit" class="btn btn-primary px-4">Crear codigo</button>
                <a href="{{ route('admin.referral-codes.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
const btn = document.getElementById('btnGenerar');
const inp = document.getElementById('inputCode');
const pre = document.getElementById('previewCode');

if (inp && pre) {
    inp.addEventListener('input', () => {
        pre.textContent = inp.value.trim().toUpperCase() || 'LOBBY-ABC123';
    });
}
if (btn && inp) {
    btn.addEventListener('click', () => {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let code = 'LOBBY-';
        for (let i = 0; i < 6; i++) code += chars[Math.floor(Math.random() * chars.length)];
        inp.value = code;
        inp.dispatchEvent(new Event('input'));
    });
}
</script>
@endsection
