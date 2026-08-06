@extends('layouts.app')
@section('title', 'Cambiar Contraseña — LOBBY69')
@section('content')

<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
  <div style="width:100%;max-width:460px;">

    {{-- Stepper --}}
    <div style="display:flex;align-items:center;justify-content:center;gap:.5rem;margin-bottom:2rem;">
      <div style="display:flex;align-items:center;gap:.4rem;opacity:.5;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--theme-accent);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;">1</div>
        <span style="font-size:.78rem;color:var(--theme-muted);">Perfil</span>
      </div>
      <div style="width:32px;height:1px;background:var(--theme-border);"></div>
      <div style="display:flex;align-items:center;gap:.4rem;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--theme-accent);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;">2</div>
        <span style="font-size:.78rem;color:var(--theme-text);font-weight:600;">Contraseña</span>
      </div>
    </div>

    {{-- Card --}}
    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:20px;overflow:hidden;">

      {{-- Header --}}
      <div style="background:linear-gradient(135deg,var(--theme-accent),#9c27b0);padding:2.5rem 2rem;text-align:center;">
        <div style="width:64px;height:64px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
          <i class="fas fa-shield-alt" style="font-size:1.8rem;color:#fff;"></i>
        </div>
        <h1 style="color:#fff;font-size:1.4rem;font-weight:800;margin:0 0 .5rem;letter-spacing:-.3px;">
          Establece tu contraseña
        </h1>
        <p style="color:rgba(255,255,255,.75);font-size:.88rem;margin:0;line-height:1.5;">
          Último paso antes de ingresar a LOBBY69.<br>
          Elige una contraseña segura y personal.
        </p>
      </div>

      {{-- Body --}}
      <div style="padding:2rem;">

        @if(session('warning'))
        <div style="background:#f59e0b15;border:1px solid #f59e0b44;color:#f59e0b;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.88rem;display:flex;align-items:center;gap:.5rem;">
          <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
        @endif

        @if(session('info'))
        <div style="background:var(--theme-accent)15;border:1px solid var(--theme-accent)44;color:var(--theme-accent);padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.88rem;display:flex;align-items:center;gap:.5rem;">
          <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background:#ef444415;border:1px solid #ef444444;color:#ef4444;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.88rem;">
          <ul style="margin:0;padding-left:1.2rem;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('password.change.store') }}">
          @csrf

          {{-- Nueva contraseña --}}
          <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.83rem;font-weight:600;color:var(--theme-text);margin-bottom:.4rem;">
              Nueva contraseña <span style="color:#ef4444;">*</span>
            </label>
            <div style="position:relative;">
              <input type="password" name="password" id="pwd"
                     placeholder="Mínimo 8 caracteres"
                     style="width:100%;padding:.75rem 3rem .75rem 1rem;border:1.5px solid var(--theme-border);border-radius:10px;font-size:.93rem;background:var(--theme-bg);color:var(--theme-text);box-sizing:border-box;transition:border-color .2s;"
                     onfocus="this.style.borderColor='var(--theme-accent)'"
                     onblur="this.style.borderColor='var(--theme-border)'"
                     required>
              <button type="button" onclick="togglePass('pwd','eye1')"
                      style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--theme-muted);">
                <i class="fas fa-eye" id="eye1"></i>
              </button>
            </div>
            {{-- Barra fortaleza --}}
            <div style="margin-top:.5rem;">
              <div style="height:3px;background:var(--theme-border);border-radius:2px;overflow:hidden;">
                <div id="strengthBar" style="height:100%;width:0;transition:all .3s;border-radius:2px;"></div>
              </div>
              <span id="strengthText" style="font-size:.72rem;color:var(--theme-muted);"></span>
            </div>
          </div>

          {{-- Confirmar --}}
          <div style="margin-bottom:1.5rem;">
            <label style="display:block;font-size:.83rem;font-weight:600;color:var(--theme-text);margin-bottom:.4rem;">
              Confirmar contraseña <span style="color:#ef4444;">*</span>
            </label>
            <div style="position:relative;">
              <input type="password" name="password_confirmation" id="pwdConfirm"
                     placeholder="Repite la contraseña"
                     style="width:100%;padding:.75rem 3rem .75rem 1rem;border:1.5px solid var(--theme-border);border-radius:10px;font-size:.93rem;background:var(--theme-bg);color:var(--theme-text);box-sizing:border-box;transition:border-color .2s;"
                     onfocus="this.style.borderColor='var(--theme-accent)'"
                     onblur="this.style.borderColor='var(--theme-border)'"
                     required>
              <button type="button" onclick="togglePass('pwdConfirm','eye2')"
                      style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--theme-muted);">
                <i class="fas fa-eye" id="eye2"></i>
              </button>
            </div>
          </div>

          {{-- Requisitos --}}
          <div style="background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:10px;padding:1rem;margin-bottom:1.5rem;">
            <div style="font-size:.78rem;font-weight:600;color:var(--theme-muted);margin-bottom:.6rem;text-transform:uppercase;letter-spacing:.05em;">
              Requisitos
            </div>
            <div id="req-len" style="font-size:.82rem;color:var(--theme-muted);margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;">
              <i class="fas fa-circle" style="font-size:.45rem;"></i> Al menos 8 caracteres
            </div>
            <div id="req-up" style="font-size:.82rem;color:var(--theme-muted);margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;">
              <i class="fas fa-circle" style="font-size:.45rem;"></i> Una letra mayúscula
            </div>
            <div id="req-low" style="font-size:.82rem;color:var(--theme-muted);margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;">
              <i class="fas fa-circle" style="font-size:.45rem;"></i> Una letra minúscula
            </div>
            <div id="req-num" style="font-size:.82rem;color:var(--theme-muted);display:flex;align-items:center;gap:.5rem;">
              <i class="fas fa-circle" style="font-size:.45rem;"></i> Un número
            </div>
          </div>

          <button type="submit" id="btnSubmit"
                  style="width:100%;padding:.9rem;background:linear-gradient(135deg,var(--theme-accent),#9c27b0);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;letter-spacing:.3px;">
            <i class="fas fa-shield-alt" style="margin-right:.5rem;"></i>Establecer contraseña y entrar
          </button>
        </form>
      </div>
    </div>

    <p style="text-align:center;color:var(--theme-muted);font-size:.75rem;margin-top:1.25rem;">
      <i class="fas fa-lock" style="margin-right:.3rem;"></i>
      Conexión segura — tus datos están protegidos
    </p>
  </div>
</div>

@push('scripts')
<script>
function togglePass(inputId, iconId) {
    var i = document.getElementById(inputId);
    var e = document.getElementById(iconId);
    if (i.type === 'password') { i.type = 'text'; e.classList.replace('fa-eye','fa-eye-slash'); }
    else { i.type = 'password'; e.classList.replace('fa-eye-slash','fa-eye'); }
}

document.getElementById('pwd').addEventListener('input', function() {
    var v = this.value;
    var checks = { len: v.length>=8, up: /[A-Z]/.test(v), low: /[a-z]/.test(v), num: /\d/.test(v) };
    Object.keys(checks).forEach(function(k) { setReq('req-'+k, checks[k]); });
    var score = Object.values(checks).filter(Boolean).length;
    var colors = ['','#ef4444','#f59e0b','#3b82f6','#22c55e'];
    var labels = ['','Muy débil','Regular','Buena','Fuerte 💪'];
    document.getElementById('strengthBar').style.cssText = 'height:100%;border-radius:2px;transition:all .3s;width:'+(score*25)+'%;background:'+colors[score]+';';
    document.getElementById('strengthText').textContent = labels[score];
    document.getElementById('strengthText').style.color = colors[score];
});

function setReq(id, ok) {
    var el = document.getElementById(id);
    el.style.color = ok ? '#22c55e' : 'var(--theme-muted)';
    var icon = el.querySelector('i');
    icon.className = ok ? 'fas fa-check-circle' : 'fas fa-circle';
    icon.style.fontSize = ok ? '.8rem' : '.45rem';
}
</script>
@endpush
@endsection