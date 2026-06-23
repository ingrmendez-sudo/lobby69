@extends('layouts.app')
@section('title', '{{ $profile->nickname ?? "Perfil" }} — LOBBY69')
@section('content')
<div style="max-width:900px;margin:2rem auto;padding:0 1rem;">

@php
    $isPairing  = $profile->profile_type === 'pareja';
    $isUnicorn  = $profile->profile_type === 'unicornio';
    $isSingle   = $profile->profile_type === 'single';
    $showName   = $profile->show_name ?? true;
    $showPName  = $profile->show_partner_name ?? true;
    $mainName   = $showName ? ($profile->display_name ?? 'Nombre oculto') : '-Nombre oculto-';
    $partName   = $showPName ? ($profile->partner_name ?? '') : '-Nombre oculto-';

    $lookingFor = json_decode($profile->looking_for ?? '[]', true) ?? [];
    $interests  = json_decode($profile->interests  ?? '[]', true) ?? [];
    $languages  = json_decode($profile->languages  ?? '[]', true) ?? [];
    $partLanguages = json_decode($profile->partner_languages ?? '[]', true) ?? [];

    $allLookingFor = ['Parejas heterosexuales','Parejas bisexuales','Parejas (ella bisexual)','Parejas (él bisexual)','Hombres heterosexuales','Hombres bisexuales','Mujeres heterosexuales','Mujeres bisexuales'];
    $allInterests  = ['Intercambio completo','Intercambio light','Sexo en grupo','Tríos','Sólo ellas','Mirar y ser vistos','Cuckold','Prácticas BDSM','Compartir fetiches','Cybersexo','Intercambio de fotos','Sexo por separado','Relaciones abiertas','Amistad','Otros'];

    // Foto de perfil
    $profilePhoto = DB::table('photos')
        ->whereRaw('user_id::text = ?', [$profile->user_id])
        ->where('is_profile_photo', true)
        ->where('status', 'approved')
        ->first();
    $profilePhotoUrl = $profilePhoto ? route('photos.serve', $profilePhoto->id) : asset('img/default-avatar.svg');

    // Fotos aprobadas del álbum público
    $photos = DB::table('photos')
        ->whereRaw('user_id::text = ?', [$profile->user_id])
        ->where('album_type', 'public')
        ->where('status', 'approved')
        ->orderBy('sort_order')
        ->get();

    $verificationStatus = DB::table('users')
        ->whereRaw('id::text = ?', [$profile->user_id])
        ->value('verification_status');
@endphp

  {{-- Header del perfil --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <div style="display:flex;gap:1.5rem;align-items:flex-start;">

      {{-- Avatar --}}
      <div style="flex-shrink:0;position:relative;">
        <img src="{{ $profilePhotoUrl }}"
             alt="{{ $profile->nickname }}"
             style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #8b5cf6;"
             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        @if($verificationStatus === 'approved')
        <div style="position:absolute;bottom:4px;right:4px;background:#3b82f6;color:white;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:.75rem;border:2px solid white;" title="Identidad verificada">✓</div>
        @endif
      </div>

      {{-- Info principal --}}
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
          <h1 style="font-size:1.6rem;font-weight:800;color:var(--color-text);margin:0;">{{ $profile->nickname }}</h1>
          @if($verificationStatus === 'approved')
          <span style="background:#dbeafe;color:#1d4ed8;padding:.2rem .7rem;border-radius:20px;font-size:.78rem;font-weight:600;">✓ Verificado</span>
          @endif
          <span style="background:#f3f4f6;color:#374151;padding:.2rem .7rem;border-radius:20px;font-size:.78rem;">
            {{ $isSingle?'Single':($isPairing?'Pareja':'Unicornio') }}
          </span>
        </div>
        <p style="color:#6b7280;font-size:.9rem;margin:.4rem 0;">
          📍 {{ implode(', ', array_filter([$profile->city, $profile->state, $profile->country])) }}
        </p>
        @if($profile->bio)
        <p style="color:#4b5563;font-size:.95rem;margin:.5rem 0 0;line-height:1.6;">{{ $profile->bio }}</p>
        @endif
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

    {{-- SOBRE ELLOS --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;">
      <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        👤 Sobre {{ $isPairing ? 'ellos' : ($isUnicorn ? 'ella/él' : 'mí') }}
      </h2>

      @if($isPairing)
      {{-- DOS COLUMNAS para pareja --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        {{-- Miembro 1 --}}
        <div>
          <h3 style="font-size:.95rem;font-weight:700;color:#8b5cf6;margin-bottom:.75rem;">
            {{ $profile->gender === 'masculino' ? '♂️' : '♀️' }} {{ $mainName }}
          </h3>
          @include('profile._physical_data', ['p' => $profile, 'isPartner' => false])
        </div>

        {{-- Miembro 2 (pareja) --}}
        @if($profile->partner_name || $profile->partner_age)
        <div>
          <h3 style="font-size:.95rem;font-weight:700;color:#ec4899;margin-bottom:.75rem;">
            {{ $profile->partner_gender === 'masculino' ? '♂️' : '♀️' }} {{ $partName }}
          </h3>
          @include('profile._physical_data', ['p' => $profile, 'isPartner' => true])
        </div>
        @endif
      </div>

      @else
      {{-- UNA COLUMNA para single/unicornio --}}
      @include('profile._physical_data', ['p' => $profile, 'isPartner' => false])
      @endif
    </div>

    {{-- BUSCAN / PARA --}}
    <div>
      {{-- Buscan --}}
      <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;margin-bottom:1.5rem;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">🔍 Buscan</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
          @foreach($allLookingFor as $opt)
          @if(in_array($opt, $lookingFor))
          <span style="font-size:.85rem;color:#8b5cf6;font-weight:600;">{{ $opt }}</span>
          @else
          <span style="font-size:.85rem;color:#d1d5db;text-decoration:line-through;">{{ $opt }}</span>
          @endif
          @endforeach
        </div>
      </div>

      {{-- Para --}}
      <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">💫 Para</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
          @foreach($allInterests as $opt)
          @if(in_array($opt, $interests))
          <span style="font-size:.85rem;color:#ec4899;font-weight:600;">{{ $opt }}</span>
          @else
          <span style="font-size:.85rem;color:#d1d5db;text-decoration:line-through;">{{ $opt }}</span>
          @endif
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Fotos --}}
  @if($photos->isNotEmpty())
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;margin-top:1.5rem;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">📸 Fotos públicas</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;">
      @foreach($photos as $photo)
      <div style="border-radius:10px;overflow:hidden;aspect-ratio:1;background:#f8fafc;">
        <img src="{{ route('photos.serve', $photo->id) }}"
             alt="{{ $photo->caption }}"
             style="width:100%;height:100%;object-fit:cover;">
      </div>
      @endforeach
    </div>
  </div>
  @endif

</div>
@endsection