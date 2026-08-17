@extends('layouts.minimal')
@section('title', 'Bienvenido a LOBBY69')
@section('content')
<section style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);">
  <div style="max-width:560px;width:100%;text-align:center;">
    <img loading="lazy" src="{{ asset('img/logo-lobby69.png') }}" alt="LOBBY69" style="height:52px;margin-bottom:2rem;" onerror="this.style.display='none'">
    <div style="background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:24px;padding:2.5rem 2rem;">
      <div style="width:80px;height:80px;background:linear-gradient(135deg,#f97068,#e84393);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2.2rem;">&#127881;</div>
      <h1 style="font-size:1.9rem;font-weight:800;color:#fff;margin-bottom:.5rem;">
        Bienvenido, {{ auth()->user()->name ?? auth()->user()->username }}!
      </h1>
      <p style="color:rgba(255,255,255,.65);font-size:1rem;margin-bottom:2rem;">
        Tu cuenta ha sido activada. Ya eres parte de <strong style="color:#f97068;">LOBBY69</strong>.
      </p>
      <div style="display:flex;flex-direction:column;gap:.85rem;margin-bottom:2rem;text-align:left;">
        <div style="display:flex;align-items:center;gap:1rem;background:rgba(255,255,255,.07);border-radius:12px;padding:.9rem 1.1rem;">
          <span style="font-size:1.4rem;flex-shrink:0;">&#128248;</span>
          <div>
            <p style="color:#fff;font-weight:600;margin:0;font-size:.9rem;">Completa tu perfil</p>
            <p style="color:rgba(255,255,255,.5);margin:0;font-size:.8rem;">Agrega una foto y descripcion para que otros te encuentren.</p>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;background:rgba(255,255,255,.07);border-radius:12px;padding:.9rem 1.1rem;">
          <span style="font-size:1.4rem;flex-shrink:0;">&#128269;</span>
          <div>
            <p style="color:#fff;font-weight:600;margin:0;font-size:.9rem;">Explora la comunidad</p>
            <p style="color:rgba(255,255,255,.5);margin:0;font-size:.8rem;">Descubre perfiles, eventos y contenido exclusivo.</p>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;background:rgba(255,255,255,.07);border-radius:12px;padding:.9rem 1.1rem;">
          <span style="font-size:1.4rem;flex-shrink:0;">&#128172;</span>
          <div>
            <p style="color:#fff;font-weight:600;margin:0;font-size:.9rem;">Conecta con personas</p>
            <p style="color:rgba(255,255,255,.5);margin:0;font-size:.8rem;">Envia mensajes y forma parte de la comunidad.</p>
          </div>
        </div>
      </div>
      <a href="{{ route('dashboard') }}" style="display:block;background:linear-gradient(135deg,#f97068,#e84393);color:#fff;padding:1rem 2rem;border-radius:50px;text-decoration:none;font-weight:700;font-size:1rem;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        Ir al Dashboard &#8594;
      </a>
      <p style="color:rgba(255,255,255,.35);font-size:.78rem;margin-top:1.25rem;">
        Tu usuario: <strong style="color:rgba(255,255,255,.6);font-family:monospace;">{{ auth()->user()->username }}</strong>
      </p>
    </div>
  </div>
</section>
@endsection