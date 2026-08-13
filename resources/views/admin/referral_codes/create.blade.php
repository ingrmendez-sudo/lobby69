@extends('layouts.admin')

@section('title', isset($referralCode) ? 'Editar Codigo' : 'Nuevo Codigo')
@section('page-title', isset($referralCode) ? 'Editar Codigo' : 'Nuevo Codigo de Referido')

@section('content')
<div style="max-width:640px;">

    <div class="mb-4">
        <a href="{{ route('admin.admin.referral-codes.index') }}"
           style="color:var(--theme-muted);font-size:.875rem;text-decoration:none;">
            &larr; Volver a codigos
        </a>
        <h4 class="mt-2 mb-0" style="color:var(--theme-text);font-weight:600;">
            @isset($referralCode)
                Editar: <span style="color:#d4af37;font-family:monospace;">{{ $referralCode->code }}</span>
            @else
                Nuevo codigo de referido
            @endisset
        </h4>
    </div>

    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:12px;padding:2rem;">
        <form method="POST"
              action="@isset($referralCode){{ route('admin.admin.referral-codes.update', $referralCode) }}@else{{ route('admin.admin.referral-codes.store') }}@endisset">
            @csrf
            @isset($referralCode) @method('PUT') @endisset

            @unless(isset($referralCode))
            {{-- Campo codigo --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Codigo de referido
                    <small style="color:var(--theme-muted);font-weight:400;margin-left:.25rem;">(se guardara en mayusculas)</small>
                </label>
                <div class="input-group">
                    <input type="text" name="code" id="inputCode"
                           class="form-control form-control-lg"
                           value="{{ old('code') }}"
                           placeholder="LOBBY-ABC123"
                           style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);font-family:monospace;letter-spacing:.05em;font-size:1rem;">
                    <button type="button" id="btnGenerar" class="btn btn-outline-secondary px-3"
                            style="border-color:var(--theme-border);color:var(--theme-muted);">
                        Generar
                    </button>
                </div>
                @error('code')
                    <div style="color:#e74c3c;font-size:.825rem;margin-top:.35rem;">{{ $message }}</div>
                @enderror
                {{-- Preview del link --}}
                <div style="margin-top:.75rem;padding:.6rem .85rem;background:rgba(212,175,55,.07);border-radius:6px;border:1px dashed rgba(212,175,55,.3);">
                    <small style="color:var(--theme-muted);display:block;margin-bottom:.2rem;">Link para compartir:</small>
                    <code id="previewLink" style="color:#d4af37;font-size:.825rem;word-break:break-all;">
                        {{ url('/invitacion') }}?ref=<span id="previewCode">LOBBY-ABC123</span>
                    </code>
                </div>
            </div>
            @endunless

            {{-- Maximo usos --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Maximo de usos
                </label>
                <input type="number" name="max_uses" min="1" max="9999"
                       class="form-control"
                       value="{{ old('max_uses', $referralCode->max_uses ?? 10) }}"
                       style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);">
                <small style="color:var(--theme-muted);display:block;margin-top:.3rem;">
                    Cuantas personas pueden registrarse con este codigo.
                </small>
            </div>

            {{-- Expiracion --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Fecha de expiracion
                    <small style="color:var(--theme-muted);font-weight:400;margin-left:.25rem;">(opcional — vacio = sin limite)</small>
                </label>
                <input type="date" name="expires_at" class="form-control"
                       value="{{ old('expires_at', isset($referralCode) && $referralCode->expires_at ? $referralCode->expires_at->format('Y-m-d') : '') }}"
                       style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);">
            </div>

            {{-- Propietario --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Asignar a usuario
                    <small style="color:var(--theme-muted);font-weight:400;margin-left:.25rem;">(opcional — para rastrear referidos)</small>
                </label>
                <select name="owner_user_id" class="form-select"
                        style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);">
                    <option value="">— Sistema (sin propietario) —</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}"
                            {{ old('owner_user_id', $referralCode->owner_user_id ?? '') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->username }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Toggle activo solo en edicion --}}
            @isset($referralCode)
            <div class="mb-4" style="padding:1rem;background:rgba(0,0,0,.04);border-radius:8px;border:1px solid var(--theme-border);">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                           name="is_active" id="is_active" value="1"
                           {{ old('is_active', $referralCode->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active"
                           style="color:var(--theme-text);font-weight:500;cursor:pointer;">
                        Codigo activo
                        <small style="display:block;color:var(--theme-muted);font-weight:400;">
                            Desactivar impide que se use sin eliminarlo.
                        </small>
                    </label>
                </div>
            </div>
            @endisset

            {{-- Botones --}}
            <div class="d-flex gap-3" style="padding-top:1.25rem;border-top:1px solid var(--theme-border);">
                <button type="submit" class="btn btn-primary px-4">
                    @isset($referralCode) Guardar cambios @else Crear codigo @endisset
                </button>
                <a href="{{ route('admin.admin.referral-codes.index') }}"
                   class="btn btn-outline-secondary px-4">Cancelar</a>
            </div>
        </form>
    </div>

    {{-- Link compartir en edicion --}}
    @isset($referralCode)
    <div style="margin-top:1rem;padding:1rem 1.25rem;background:rgba(212,175,55,.07);border-radius:8px;border:1px dashed rgba(212,175,55,.3);">
        <small style="color:var(--theme-muted);display:block;margin-bottom:.35rem;font-weight:600;">Link para compartir:</small>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <code style="color:#d4af37;font-size:.875rem;">{{ url('/invitacion') }}?ref={{ $referralCode->code }}</code>
            <button onclick="navigator.clipboard.writeText('{{ url('/invitacion') }}?ref={{ $referralCode->code }}').then(()=>{this.textContent='Copiado!';setTimeout(()=>this.textContent='Copiar',2000)})"
                    style="padding:.25rem .7rem;border-radius:5px;border:1px solid rgba(212,175,55,.4);color:#d4af37;background:transparent;font-size:.775rem;cursor:pointer;">
                Copiar
            </button>
        </div>
    </div>
    @endisset

</div>

<script>
const btn = document.getElementById('btnGenerar');
const inp = document.getElementById('inputCode');
const pre = document.getElementById('previewCode');
if (inp && pre) {
    inp.addEventListener('input', () => pre.textContent = inp.value || 'LOBBY-ABC123');
}
if (btn && inp) {
    btn.addEventListener('click', () => {
        const c = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let r = 'LOBBY-';
        for (let i = 0; i < 6; i++) r += c[Math.floor(Math.random() * c.length)];
        inp.value = r;
        inp.dispatchEvent(new Event('input'));
    });
}
</script>
@endsection
