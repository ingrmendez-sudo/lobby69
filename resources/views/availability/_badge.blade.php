@props(['availability' => null, 'size' => 'normal'])

@if($availability && \Carbon\Carbon::parse($availability->expires_at)->isFuture())
@php
    $mins  = max(0, (int) now()->diffInMinutes($availability->expires_at, false));
    $hrs   = floor($mins / 60);
    $rem   = $mins % 60;
    $label = $mins < 60
        ? "{$mins}min"
        : ($rem > 0 ? "{$hrs}h {$rem}m" : "{$hrs}h");
    $small = $size === 'small';
@endphp
<span class="avail-badge {{ $small ? 'avail-badge--sm' : '' }}"
      title="{{ $availability->message ?? 'Disponible ahora' }} · Expira en {{ $label }}">
    <span class="avail-badge__dot"></span>
    <span class="avail-badge__text">
        @if(!$small) Disponible · @endif{{ $label }}
    </span>
</span>
@endif