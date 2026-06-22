@extends('layouts.app')
@section('title', 'Nueva Contraseña — LOBBY69')
@section('content')
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
  <div style="width:100%;max-width:420px;">
    <div style="background:white;border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.12);overflow:hidden;">

      <div style="background:linear-gradient(135deg,#667eea,#764ba2);padding:2.5rem 2rem;text-align:center;">
        <div style="width:64px;height:64px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
          <i class="fas fa-lock-open" style="font-size:1.8rem;color:white;"></i>
        </div>
        <h1 style="color:white;font-size:1.5rem;font-weight:700;margin:0 0 .5rem;">Nueva Contraseña</h1>
        <p style="color:rgba(255,255,255,.8);font-size:.9rem;margin:0;">Elige una contraseña segura.</p>
      </div>

      <div style="padding:2rem;">
        @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.9rem;">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('password.reset.store') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <input type="hidden" name="email" value="{{ $email }}">

          <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.5rem;">Nueva contraseña</label>
            <input type="password" name="password" placeholder="Mínimo 8 caracteres"
                   style="width:100%;padding:.75rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;"
                   required>
          </div>
          <div style="margin-bottom:1.75rem;">
            <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.5rem;">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" placeholder="Repite la contraseña"
                   style="width:100%;padding:.75rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;"
                   required>
          </div>
          <button type="submit"
                  style="width:100%;padding:.875rem;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;">
            <i class="fas fa-shield-alt" style="margin-right:.5rem;"></i>Establecer contraseña
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection