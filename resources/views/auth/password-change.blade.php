@extends('layouts.app')
@section('title', 'Cambiar Contraseña — LOBBY69')
@section('content')

<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
  <div style="width:100%;max-width:440px;">

    {{-- Card --}}
    <div style="background:white;border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.12);overflow:hidden;">

      {{-- Header --}}
      <div style="background:linear-gradient(135deg,#667eea,#764ba2);padding:2.5rem 2rem;text-align:center;">
        <div style="width:64px;height:64px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
          <i class="fas fa-lock" style="font-size:1.8rem;color:white;"></i>
        </div>
        <h1 style="color:white;font-size:1.5rem;font-weight:700;margin:0 0 .5rem;">Cambia tu contraseña</h1>
        <p style="color:rgba(255,255,255,.8);font-size:.9rem;margin:0;">
          Estás usando una contraseña temporal.<br>Elige una contraseña segura para continuar.
        </p>
      </div>

      {{-- Body --}}
      <div style="padding:2rem;">

        {{-- Alerta warning --}}
        @if(session('warning'))
        <div style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.9rem;display:flex;align-items:center;gap:.5rem;">
          <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
        @endif

        {{-- Errores --}}
        @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.9rem;">
          <ul style="margin:0;padding-left:1.2rem;">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('password.change.store') }}" id="formCambio">
          @csrf

          {{-- Nueva contraseña --}}
          <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.5rem;">
              Nueva contraseña
            </label>
            <div style="position:relative;">
              <input type="password"
                     name="password"
                     id="password"
                     placeholder="Mínimo 8 caracteres"
                     style="width:100%;padding:.75rem 3rem .75rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;transition:border-color .2s;"
                     onfocus="this.style.borderColor='#667eea'"
                     onblur="this.style.borderColor='#e5e7eb'"
                     required>
              <button type="button" onclick="togglePass('password','eyePass')"
                      style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                <i class="fas fa-eye" id="eyePass"></i>
              </button>
            </div>
            {{-- Indicador de fortaleza --}}
            <div style="margin-top:.5rem;">
              <div style="height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden;">
                <div id="strengthBar" style="height:100%;width:0;transition:all .3s;border-radius:2px;"></div>
              </div>
              <span id="strengthText" style="font-size:.75rem;color:#9ca3af;"></span>
            </div>
          </div>

          {{-- Confirmar contraseña --}}
          <div style="margin-bottom:1.75rem;">
            <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.5rem;">
              Confirmar contraseña
            </label>
            <div style="position:relative;">
              <input type="password"
                     name="password_confirmation"
                     id="password_confirmation"
                     placeholder="Repite la contraseña"
                     style="width:100%;padding:.75rem 3rem .75rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;transition:border-color .2s;"
                     onfocus="this.style.borderColor='#667eea'"
                     onblur="this.style.borderColor='#e5e7eb'"
                     required>
              <button type="button" onclick="togglePass('password_confirmation','eyeConfirm')"
                      style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                <i class="fas fa-eye" id="eyeConfirm"></i>
              </button>
            </div>
          </div>

          {{-- Requisitos --}}
          <div style="background:#f8fafc;border-radius:10px;padding:1rem;margin-bottom:1.5rem;font-size:.85rem;">
            <div style="font-weight:600;color:#374151;margin-bottom:.5rem;">La contraseña debe tener:</div>
            <div id="req-len"  style="color:#9ca3af;margin-bottom:.25rem;"><i class="fas fa-circle" style="font-size:.5rem;margin-right:.5rem;"></i>Al menos 8 caracteres</div>
            <div id="req-up"   style="color:#9ca3af;margin-bottom:.25rem;"><i class="fas fa-circle" style="font-size:.5rem;margin-right:.5rem;"></i>Una letra mayúscula</div>
            <div id="req-low"  style="color:#9ca3af;margin-bottom:.25rem;"><i class="fas fa-circle" style="font-size:.5rem;margin-right:.5rem;"></i>Una letra minúscula</div>
            <div id="req-num"  style="color:#9ca3af;"><i class="fas fa-circle" style="font-size:.5rem;margin-right:.5rem;"></i>Un número</div>
          </div>

          <button type="submit" id="btnSubmit"
                  style="width:100%;padding:.875rem;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:opacity .2s;">
            <i class="fas fa-shield-alt" style="margin-right:.5rem;"></i>Establecer contraseña
          </button>
        </form>
      </div>
    </div>

    {{-- Info extra --}}
    <p style="text-align:center;color:#9ca3af;font-size:.8rem;margin-top:1.5rem;">
      <i class="fas fa-lock" style="margin-right:.3rem;"></i>
      Conexión segura — tus datos están protegidos
    </p>
  </div>
</div>

@push('scripts')
<script>
function togglePass(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}

document.getElementById('password').addEventListener('input', function() {
    var val = this.value;
    var len = val.length >= 8;
    var up  = /[A-Z]/.test(val);
    var low = /[a-z]/.test(val);
    var num = /\d/.test(val);

    setReq('req-len', len);
    setReq('req-up',  up);
    setReq('req-low', low);
    setReq('req-num', num);

    var score = [len,up,low,num].filter(Boolean).length;
    var bar   = document.getElementById('strengthBar');
    var txt   = document.getElementById('strengthText');
    var colors = ['','#ef4444','#f59e0b','#3b82f6','#10b981'];
    var labels = ['','Muy débil','Regular','Buena','Fuerte'];
    bar.style.width = (score * 25) + '%';
    bar.style.background = colors[score];
    txt.textContent = labels[score];
    txt.style.color = colors[score];
});

function setReq(id, ok) {
    var el = document.getElementById(id);
    el.style.color = ok ? '#10b981' : '#9ca3af';
    el.querySelector('i').className = ok ? 'fas fa-check-circle' : 'fas fa-circle';
    el.querySelector('i').style.fontSize = ok ? '.8rem' : '.5rem';
}
</script>
@endpush
@endsection