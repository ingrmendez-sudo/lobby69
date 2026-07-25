@extends('layouts.app')
@section('title', 'Perfil privado')
@section('content')
<div style="max-width:480px;margin:4rem auto;text-align:center;padding:2rem;
            background:var(--card-bg,#fff);border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,.08)">
    <div style="font-size:3rem;margin-bottom:1rem">🔒</div>
    <h4 style="color:var(--text-main,#222);margin-bottom:.5rem">Perfil privado</h4>
    <p style="color:var(--text-muted,#888);font-size:.9rem;margin-bottom:1.5rem">
        Este perfil solo es visible para sus seguidores.<br>
        Síguelo para solicitar acceso.
    </p>
    <a href="{{ url()->previous() }}"
       style="display:inline-block;background:var(--bs-pink,#e91e8c);color:#fff;
              border-radius:10px;padding:8px 24px;text-decoration:none;font-weight:600;font-size:.9rem">
        ← Volver
    </a>
</div>
@endsection