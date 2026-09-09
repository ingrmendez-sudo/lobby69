@extends('layouts.admin')

@section('title', 'Editar Codigo: ' . $referralCode->code)
@section('page-title', 'Editar Codigo de Referido')

@section('content')
<div style="max-width:640px;">

    <div class="mb-4">
        <a href="{{ route('admin.referral-codes.index') }}"
           style="color:var(--theme-muted);font-size:.875rem;text-decoration:none;">
            &larr; Volver a codigos
        </a>
        <h4 class="mt-2 mb-0" style="color:var(--theme-text);font-weight:600;">
            Editar: <span style="color:#d4af37;font-family:monospace;">{{ $referralCode->code }}</span>
        </h4>
    </div>

    {{-- Panel de comparticion --}}
    <div style="margin-bottom:1.25rem;padding:1.25rem;background:rgba(212,175,55,.07);border-radius:10px;border:1px dashed rgba(212,175,55,.35);">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <small style="color:var(--theme-muted);font-weight:600;display:block;margin-bottom:.4rem;">🔗 Link de invitacion</small>
                <code id="shareLink" style="color:#d4af37;font-size:.875rem;word-break:break-all;">{{ url('/invitacion') }}?ref={{ $referralCode->code }}</code>
            </div>
            <div class="d-flex gap-2 flex-shrink-0">
                {{-- Copiar --}}
                <button id="btnCopiar" onclick="copiarLink()"
                        style="padding:.4rem .9rem;border-radius:7px;border:1px solid rgba(212,175,55,.45);color:#d4af37;background:transparent;font-size:.825rem;cursor:pointer;white-space:nowrap;">
                    📋 Copiar
                </button>
                {{-- Compartir nativo (solo si disponible) --}}
                <button id="btnCompartir" onclick="compartirNativo()" style="display:none;padding:.4rem .9rem;border-radius:7px;border:1px solid rgba(124,58,237,.5);color:#a78bfa;background:transparent;font-size:.825rem;cursor:pointer;white-space:nowrap;">
                    📤 Compartir
                </button>
            </div>
        </div>
        {{-- Canales de comparticion directa --}}
        <div style="margin-top:1rem;padding-top:.85rem;border-top:1px solid rgba(212,175,55,.2);">
            <small style="color:var(--theme-muted);display:block;margin-bottom:.6rem;">Compartir directamente en:</small>
            <div class="d-flex gap-2 flex-wrap">
                <a id="linkWhatsapp" href="#" target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .8rem;border-radius:7px;background:rgba(37,211,102,.12);color:#25d366;border:1px solid rgba(37,211,102,.3);font-size:.8rem;text-decoration:none;font-weight:600;">
                    WhatsApp
                </a>
                <a id="linkTelegram" href="#" target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .8rem;border-radius:7px;background:rgba(0,136,204,.12);color:#0088cc;border:1px solid rgba(0,136,204,.3);font-size:.8rem;text-decoration:none;font-weight:600;">
                    Telegram
                </a>
                <a id="linkTwitter" href="#" target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .8rem;border-radius:7px;background:rgba(29,155,240,.12);color:#1d9bf0;border:1px solid rgba(29,155,240,.3);font-size:.8rem;text-decoration:none;font-weight:600;">
                    X / Twitter
                </a>
                <a id="linkEmail" href="#"
                   style="display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .8rem;border-radius:7px;background:rgba(255,255,255,.05);color:var(--theme-muted);border:1px solid var(--theme-border);font-size:.8rem;text-decoration:none;font-weight:600;">
                    Email
                </a>
            </div>
        </div>
    </div>

    {{-- Formulario edicion --}}
    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:12px;padding:2rem;">
        <form method="POST" action="{{ route('admin.referral-codes.update', $referralCode) }}">
            @csrf @method('PUT')

            {{-- Maximo usos --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">Maximo de usos</label>
                <input type="number" name="max_uses" min="1" max="9999"
                       class="form-control @error('max_uses') is-invalid @enderror"
                       value="{{ old('max_uses', $referralCode->max_uses) }}"
                       style="background:var(--theme-card);color:var(--theme-text);border-color:var(--theme-border);">
                @error('max_uses')
                    <div style="color:#e74c3c;font-size:.825rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
                <small style="color:var(--theme-muted);display:block;margin-top:.3rem;">
                    Usos actuales: <strong style="color:var(--theme-text);">{{ $referralCode->uses_count }}</strong>
                </small>
            </div>

            {{-- Expiracion --}}
            <div class="mb-4">
                <label style="display:block;color:var(--theme-text);font-weight:600;margin-bottom:.5rem;font-size:.9rem;">
                    Fecha de expiracion
                    <small style="color:var(--theme-muted);font-weight:400;margin-left:.25rem;">(opcional)</small>
                </label>
                <input type="date" name="expires_at" class="form-control"
                       value="{{ old('expires_at', $referralCode->expires_at ? $referralCode->expires_at->format('Y-m-d') : '') }}"
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
                        <option value="{{ $admin->id }}"
                            {{ old('owner_user_id', $referralCode->owner_user_id) == $admin->id ? 'selected' : '' }}>
                            {{ $admin->username }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Toggle activo --}}
            <div class="mb-4" style="padding:1rem;background:rgba(0,0,0,.04);border-radius:8px;border:1px solid var(--theme-border);">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                           name="is_active" id="is_active" value="1"
                           {{ old('is_active', $referralCode->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active"
                           style="color:var(--theme-text);font-weight:500;cursor:pointer;">
                        Codigo activo
                        <small style="display:block;color:var(--theme-muted);font-weight:400;">Desactivar impide que se use sin eliminarlo.</small>
                    </label>
                </div>
            </div>

            {{-- Botones --}}
            <div class="d-flex gap-3" style="padding-top:1.25rem;border-top:1px solid var(--theme-border);">
                <button type="submit" class="btn btn-primary px-4">Guardar cambios</button>
                <a href="{{ route('admin.referral-codes.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
            </div>
        </form>
    </div>
</div>

{{-- Toast --}}
<div id="rc-toast" style="display:none;position:fixed;bottom:28px;left:50%;transform:translateX(-50%);background:#7c3aed;color:#fff;padding:10px 22px;border-radius:10px;font-size:.875rem;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.35);opacity:0;transition:opacity .3s;">
    📋 Link copiado al portapapeles
</div>

<script>
const SHARE_URL  = '{{ url("/invitacion") }}?ref={{ $referralCode->code }}';
const SHARE_CODE = '{{ $referralCode->code }}';
const SHARE_TEXT = encodeURIComponent('¡Únete a Lobby69 con mi código de invitación!\n\nCódigo: ' + SHARE_CODE + '\n' + SHARE_URL);

// Inicializar links directos
document.getElementById('linkWhatsapp').href  = 'https://wa.me/?text=' + SHARE_TEXT;
document.getElementById('linkTelegram').href  = 'https://t.me/share/url?url=' + encodeURIComponent(SHARE_URL) + '&text=' + encodeURIComponent('¡Únete a Lobby69! Código: ' + SHARE_CODE);
document.getElementById('linkTwitter').href   = 'https://twitter.com/intent/tweet?text=' + SHARE_TEXT;
document.getElementById('linkEmail').href     = 'mailto:?subject=' + encodeURIComponent('Invitación a Lobby69') + '&body=' + SHARE_TEXT;

// Mostrar boton nativo solo si disponible
if (navigator.share) {
    document.getElementById('btnCompartir').style.display = 'inline-flex';
}

function compartirNativo() {
    navigator.share({
        title: 'Invitación a Lobby69',
        text:  '¡Únete a Lobby69! Código: ' + SHARE_CODE,
        url:   SHARE_URL
    }).catch(() => {});
}

function copiarLink() {
    const btn = document.getElementById('btnCopiar');
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(SHARE_URL).then(() => {
            btn.textContent = '✅ Copiado';
            setTimeout(() => btn.textContent = '📋 Copiar', 2000);
            mostrarToast();
        }).catch(() => copiarLegacy(btn));
    } else {
        copiarLegacy(btn);
    }
}

function copiarLegacy(btn) {
    const el = document.createElement('textarea');
    el.value = SHARE_URL;
    el.style.cssText = 'position:fixed;left:-9999px;top:-9999px;';
    document.body.appendChild(el);
    el.focus(); el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    btn.textContent = '✅ Copiado';
    setTimeout(() => btn.textContent = '📋 Copiar', 2000);
    mostrarToast();
}

function mostrarToast() {
    const t = document.getElementById('rc-toast');
    t.style.display = 'block';
    requestAnimationFrame(() => t.style.opacity = '1');
    setTimeout(() => {
        t.style.opacity = '0';
        setTimeout(() => t.style.display = 'none', 300);
    }, 2500);
}
</script>
@endsection
