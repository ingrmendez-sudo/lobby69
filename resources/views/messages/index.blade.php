@extends('layouts.app')
@section('title', 'Mensajes · LOBBY69')

@php
    $userId    = (string) \App\Models\User::find(auth()->id())->id ?? "";
    $u         = auth()->user();
    $mt        = $u ? $u->membership_type : null;
    $esMiembro = $mt && $mt !== "free";
@endphp


@push('sidebar-right')
{{-- Sin sidebar derecho en esta página — el split-view ocupa todo el ancho --}}
@endpush

@push('styles')
<style>
/* ══════════════════════════════════════
   MODAL UPGRADE — Limite de mensajes
   ══════════════════════════════════════ */
#upgradeOverlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(10, 10, 20, 0.85);
    backdrop-filter: blur(6px);
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
#upgradeOverlay.is-visible { display: flex; }
</style>
{{-- Sin sidebar derecho en esta página — el split-view ocupa todo el ancho --}}

/* ══════════════════════════════════════
   MODAL UPGRADE — Limite de mensajes
   ══════════════════════════════════════ */
#upgradeOverlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(10, 10, 20, 0.85);
    backdrop-filter: blur(6px);
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
#upgradeOverlay.is-visible {
    display: flex;
}
.upgrade-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 20px;
    padding: 2.5rem 2rem;
    max-width: 520px;
    width: 100%;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(139,92,246,0.1);
    animation: upgradeIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes upgradeIn {
    from { opacity: 0; transform: scale(0.85) translateY(20px); }
    to   { opacity: 1; transform: scale(1)    translateY(0); }
}
.upgrade-crown {
    font-size: 3rem;
    margin-bottom: 0.5rem;
    display: block;
    animation: crownPulse 2s ease-in-out infinite;
}
@keyframes crownPulse {
    0%, 100% { transform: scale(1) rotate(-3deg); }
    50%       { transform: scale(1.1) rotate(3deg); }
}
.upgrade-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.4rem;
}
.upgrade-subtitle {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 1.25rem;
}
.upgrade-progress {
    background: rgba(255,255,255,0.1);
    border-radius: 999px;
    height: 8px;
    margin-bottom: 1.75rem;
    overflow: hidden;
}
.upgrade-progress__bar {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #8b5cf6, #ec4899);
    transition: width 0.5s ease;
}
.upgrade-plans {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.875rem;
    margin-bottom: 1.5rem;
    text-align: left;
}
.upgrade-plan {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 1rem;
}
.upgrade-plan.is-featured {
    background: rgba(139,92,246,0.15);
    border-color: rgba(139,92,246,0.5);
}
.upgrade-plan__name {
    font-size: 0.8rem;
    font-weight: 700;
    color: #a78bfa;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}
.upgrade-plan.is-featured .upgrade-plan__name { color: #ec4899; }
.upgrade-plan__perk {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    margin-bottom: 0.3rem;
    line-height: 1.3;
}
.upgrade-plan__perk::before {
    content: '✓';
    color: #a78bfa;
    font-weight: 700;
    flex-shrink: 0;
}
.upgrade-plan.is-featured .upgrade-plan__perk::before { color: #ec4899; }
.upgrade-cta {
    display: block;
    width: 100%;
    padding: 0.875rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    text-decoration: none;
    margin-bottom: 0.75rem;
    transition: opacity 0.2s, transform 0.2s;
}
.upgrade-cta:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }
.upgrade-dismiss {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.4);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.2s;
}
.upgrade-dismiss:hover { color: rgba(255,255,255,0.7); }
@endpush

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════
   LOBBY69 — DARK HUB · Chat redesign v3
   Layout: sidebar-canales | columna-central | panel-online
   ══════════════════════════════════════════════════════════════ */

/* ── Variables ── */
:root {
    --ch-accent:       #8b5cf6;
    --ch-accent2:      #4f46e5;
    --ch-accent-glow:  rgba(139,92,246,0.20);
    --ch-gold:         #f59e0b;
    --ch-gold2:        #ef4444;
    --ch-online:       #4ade80;
    --ch-sidebar-w:    268px;
    --ch-right-w:      232px;
    --ch-header-h:     56px;
    --ch-border:       var(--theme-border);
    --ch-surface:      var(--theme-card);
    --ch-bg:           var(--theme-bg);
    --ch-text:         var(--theme-text);
    --ch-muted:        var(--theme-muted);
    --ch-bubble-own:   linear-gradient(135deg,#7c3aed,#4f46e5);
    --ch-bubble-in:    var(--theme-card-alt);
    --ch-radius:       14px;
}

/* ══ OVERRIDE layout global ══ */
body.page-mensajes,
body.page-mensajes .l69-layout,
body.page-mensajes .l69-layout__content,
body.page-mensajes main,
body.page-mensajes > main,
body.page-mensajes .l69-main {
    display: block !important;
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    gap: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    min-height: 0 !important;
    overflow: hidden !important;
}
body.page-mensajes .l69-sidebar--left,
body.page-mensajes .l69-sidebar--right {
    display: none !important;
}

/* ══ WRAPPER PRINCIPAL ══ */
.ch-wrap {
    display: grid;
    grid-template-columns: var(--ch-sidebar-w) 1fr var(--ch-right-w);
    grid-template-rows: 1fr;
    position: fixed;
    top: var(--nav-h, 64px);
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: calc(100vh - var(--nav-h, 64px));
    overflow: hidden;
    background: var(--ch-bg);
    z-index: 50;
}

/* ══════════════════════════════════════════
   PANEL IZQUIERDO — Sidebar canales + DMs
   ══════════════════════════════════════════ */
.ch-sidebar {
    display: flex;
    flex-direction: column;
    background: var(--ch-surface);
    border-right: 1px solid var(--ch-border);
    overflow: hidden;
    min-width: 0;
}

/* Header sidebar */
.ch-sidebar__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1rem;
    height: var(--ch-header-h);
    border-bottom: 1px solid var(--ch-border);
    flex-shrink: 0;
}
.ch-sidebar__title {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--ch-muted);
}

/* Search */
.ch-search {
    padding: 0.6rem 0.75rem;
    flex-shrink: 0;
    border-bottom: 1px solid var(--ch-border);
}
.ch-search__inner {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--ch-bg);
    border: 1px solid var(--ch-border);
    border-radius: 8px;
    padding: 0.4rem 0.65rem;
}
.ch-search__inner i { color: var(--ch-muted); font-size: 0.78rem; }
.ch-search__input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    font-size: 0.82rem;
    color: var(--ch-text);
    min-width: 0;
}
.ch-search__input::placeholder { color: var(--ch-muted); }

/* Secciones del sidebar */
.ch-section {
    flex-shrink: 0;
}
.ch-section__label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.7rem 0.75rem 0.3rem;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ch-muted);
    cursor: pointer;
    user-select: none;
}
.ch-section__label:hover { color: var(--ch-text); }
.ch-section__label i {
    font-size: 0.6rem;
    transition: transform 0.2s;
}

/* Canal row */
.ch-channel {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.38rem 0.75rem;
    border-radius: 6px;
    margin: 0 0.4rem;
    cursor: pointer;
    transition: background 0.15s;
    font-size: 0.875rem;
    color: var(--ch-muted);
    text-decoration: none;
    position: relative;
}
.ch-channel:hover {
    background: var(--theme-hover);
    color: var(--ch-text);
}
.ch-channel.is-active {
    background: var(--ch-accent-glow);
    color: var(--ch-accent);
    font-weight: 600;
}
.ch-channel__hash {
    font-size: 1rem;
    opacity: 0.7;
    flex-shrink: 0;
    width: 18px;
    text-align: center;
}
.ch-channel__name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ch-channel__dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--ch-online);
    box-shadow: 0 0 6px var(--ch-online);
    flex-shrink: 0;
    animation: ch-pulse 2s infinite;
}
@keyframes ch-pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.ch-channel__badge {
    background: var(--ch-accent);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 999px;
    flex-shrink: 0;
}

/* Lista de DMs (scroll) */
.ch-dm-list {
    flex: 1;
    overflow-y: auto;
    padding-bottom: 0.5rem;
}
.ch-dm-list::-webkit-scrollbar { width: 4px; }
.ch-dm-list::-webkit-scrollbar-track { background: transparent; }
.ch-dm-list::-webkit-scrollbar-thumb { background: var(--ch-border); border-radius: 4px; }

/* DM row */
.ch-dm {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    margin: 0 0.4rem;
    cursor: pointer;
    transition: background 0.15s;
}
.ch-dm:hover { background: var(--theme-hover); }
.ch-dm.is-active { background: var(--ch-accent-glow); }
.ch-dm__avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--ch-accent-glow);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 700;
    color: var(--ch-accent);
    flex-shrink: 0;
    overflow: hidden;
    position: relative;
}
.ch-dm__avatar img { width: 100%; height: 100%; object-fit: cover; }
.ch-dm__online-dot {
    position: absolute;
    bottom: 0; right: 0;
    width: 9px; height: 9px;
    border-radius: 50%;
    background: #6b7280;
    border: 2px solid var(--ch-surface);
}
.ch-dm__online-dot.is-online { background: var(--ch-online); }
.ch-dm__info { flex: 1; min-width: 0; }
.ch-dm__name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ch-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ch-dm__preview {
    font-size: 0.72rem;
    color: var(--ch-muted);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ch-dm__meta {
    display: flex; flex-direction: column; align-items: flex-end; gap: 2px; flex-shrink: 0;
}
.ch-dm__time { font-size: 0.65rem; color: var(--ch-muted); }
.ch-dm__count {
    background: var(--ch-accent);
    color: #fff;
    font-size: 0.65rem; font-weight: 700;
    padding: 1px 5px; border-radius: 999px;
    min-width: 16px; text-align: center;
}

/* Empty state sidebar */
.ch-dm-empty {
    padding: 1.5rem 1rem;
    text-align: center;
    color: var(--ch-muted);
    font-size: 0.8rem;
}
.ch-dm-empty__icon { font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.4; }
.ch-dm-empty a {
    color: var(--ch-accent);
    text-decoration: none;
    font-weight: 600;
}

/* ══════════════════════════════════════════
   COLUMNA CENTRAL
   ══════════════════════════════════════════ */
.ch-main {
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
    background: var(--ch-bg);
    position: relative;
    overflow: hidden;
}

/* Header canal activo */
.ch-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0 1.25rem;
    height: var(--ch-header-h);
    background: var(--ch-surface);
    border-bottom: 1px solid var(--ch-border);
    flex-shrink: 0;
    z-index: 10;
}
.ch-header__icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
    background: var(--ch-accent-glow);
    color: var(--ch-accent);
}
.ch-header__icon--sala {
    background: linear-gradient(135deg,#7c3aed,#4f46e5);
    color: #fff;
}
.ch-header__info { flex: 1; min-width: 0; }
.ch-header__name {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--ch-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ch-header__sub {
    font-size: 0.72rem;
    color: var(--ch-muted);
    display: flex; align-items: center; gap: 0.35rem;
}
.ch-header__live-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--ch-online);
    box-shadow: 0 0 5px var(--ch-online);
    animation: ch-pulse 2s infinite;
}
.ch-header__actions { display: flex; gap: 0.35rem; }
.ch-header__btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: none;
    background: none;
    color: var(--ch-muted);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    transition: background 0.15s, color 0.15s;
    text-decoration: none;
}
.ch-header__btn:hover {
    background: var(--theme-hover);
    color: var(--ch-text);
}
.ch-header__back { display: none; }

/* Área de mensajes */
.ch-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    scroll-behavior: smooth;
    min-height: 0;
}
.ch-messages::-webkit-scrollbar { width: 5px; }
.ch-messages::-webkit-scrollbar-track { background: transparent; }
.ch-messages::-webkit-scrollbar-thumb { background: var(--ch-border); border-radius: 4px; }

/* Burbujas mensajes privados */
.ch-bubble-wrap {
    display: flex;
    flex-direction: column;
    max-width: 68%;
    align-self: flex-start;
    margin-bottom: 0.15rem;
}
.ch-bubble-wrap.is-own { align-self: flex-end; text-align: right; }
.ch-bubble__author {
    font-size: 0.68rem;
    color: var(--ch-muted);
    margin-bottom: 3px;
    padding: 0 0.4rem;
}
.ch-bubble {
    padding: 0.5rem 0.875rem;
    border-radius: 0 var(--ch-radius) var(--ch-radius) var(--ch-radius);
    font-size: 0.875rem;
    line-height: 1.45;
    word-break: break-word;
    background: var(--ch-bubble-in);
    color: var(--ch-text);
    border: 1px solid var(--ch-border);
    animation: ch-bubble-in 0.18s ease;
}
@keyframes ch-bubble-in {
    from { opacity:0; transform:translateY(4px); }
    to   { opacity:1; transform:translateY(0); }
}
.ch-bubble-wrap.is-own .ch-bubble {
    background: var(--ch-bubble-own);
    color: #fff;
    border-color: transparent;
    border-radius: var(--ch-radius) var(--ch-radius) 0 var(--ch-radius);
}
.ch-bubble__time {
    font-size: 0.62rem;
    color: var(--ch-muted);
    margin-top: 3px;
    padding: 0 0.4rem;
}

/* Fecha separador */
.ch-date-sep {
    text-align: center;
    font-size: 0.7rem;
    color: var(--ch-muted);
    padding: 0.5rem 0;
    display: flex; align-items: center; gap: 0.75rem;
}
.ch-date-sep::before,.ch-date-sep::after {
    content:''; flex:1; height:1px; background: var(--ch-border);
}

/* Sistema sala */
.ch-sys-msg {
    text-align: center;
    font-size: 0.72rem;
    color: var(--ch-muted);
    padding: 0.3rem 0;
    font-style: italic;
}

/* Loader */
.ch-loader {
    display: flex; align-items: center; justify-content: center;
    gap: 0.5rem; color: var(--ch-muted); font-size: 0.82rem;
    padding: 2rem;
}
.ch-loader i { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Typing */
.ch-typing {
    display: none;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    font-size: 0.72rem;
    color: var(--ch-muted);
    font-style: italic;
}
.ch-typing.is-visible { display: flex; }
.ch-typing-dots span {
    display: inline-block; width: 5px; height: 5px;
    border-radius: 50%; background: var(--ch-muted);
    animation: ch-dot 1.2s infinite;
}
.ch-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.ch-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes ch-dot { 0%,80%,100%{transform:scale(0.8);opacity:0.5} 40%{transform:scale(1.1);opacity:1} }

/* Gate freemium en sala */
.ch-gate {
    margin: 0;
    padding: 1rem 1.25rem;
    background: linear-gradient(to bottom,
        rgba(124,58,237,0.06) 0%,
        rgba(124,58,237,0.12) 100%);
    border-top: 1px solid var(--ch-border);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
    text-align: center;
    flex-shrink: 0;
}
.ch-gate__top {
    display: flex; align-items: center; gap: 0.75rem;
    width: 100%;
}
.ch-gate__avatar-stack {
    display: flex; flex-direction: row-reverse;
}
.ch-gate__avatar-stack span {
    width: 24px; height: 24px;
    border-radius: 50%;
    background: var(--ch-accent-glow);
    border: 2px solid var(--ch-surface);
    margin-left: -8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; font-weight: 700; color: var(--ch-accent);
}
.ch-gate__txt {
    flex: 1; text-align: left;
    font-size: 0.8rem; color: var(--ch-muted);
}
.ch-gate__txt strong { color: var(--ch-text); display: block; font-size: 0.85rem; }
.ch-gate__cta {
    width: 100%;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    padding: 0.65rem 1.5rem;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: #fff !important;
    border-radius: 10px;
    font-weight: 700; font-size: 0.875rem;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.15s;
    box-shadow: 0 4px 16px rgba(245,158,11,0.3);
}
.ch-gate__cta:hover { opacity: 0.9; transform: translateY(-1px); }

/* Input área */
.ch-input-area {
    display: flex;
    flex-direction: column;
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--ch-border);
    background: var(--ch-surface);
    flex-shrink: 0;
    gap: 0.5rem;
}
.ch-input-wrap {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    background: var(--ch-bg);
    border: 1px solid var(--ch-border);
    border-radius: 10px;
    padding: 0.5rem 0.75rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.ch-input-wrap:focus-within {
    border-color: var(--ch-accent);
    box-shadow: 0 0 0 3px var(--ch-accent-glow);
}
.ch-input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    resize: none;
    font-size: 0.875rem;
    color: var(--ch-text);
    line-height: 1.45;
    max-height: 120px;
    min-height: 22px;
    font-family: inherit;
}
.ch-input::placeholder { color: var(--ch-muted); }
.ch-send-btn {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: var(--ch-accent);
    border: none;
    color: #fff;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    transition: opacity 0.2s, transform 0.15s;
    flex-shrink: 0;
}
.ch-send-btn:hover:not(:disabled) { opacity: 0.85; transform: scale(1.05); }
.ch-send-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Membership counter (usuarios libres) */
.ch-msg-counter {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.75rem; color: var(--ch-muted);
}
.ch-msg-counter__num { font-weight: 700; color: var(--ch-accent); }
.ch-msg-counter__upgrade {
    margin-left: auto;
    color: var(--ch-gold);
    font-weight: 600; font-size: 0.72rem;
    text-decoration: none;
    animation: ch-gold-pulse 1.5s infinite;
}
@keyframes ch-gold-pulse { 0%,100%{opacity:1} 50%{opacity:0.55} }

/* ══════════════════════════════════════════
   PANEL DERECHO — Online + Upgrade
   ══════════════════════════════════════════ */
.ch-panel {
    display: flex;
    flex-direction: column;
    background: var(--ch-surface);
    border-left: 1px solid var(--ch-border);
    overflow-y: auto;
    overflow-x: hidden;
}
.ch-panel::-webkit-scrollbar { width: 4px; }
.ch-panel::-webkit-scrollbar-thumb { background: var(--ch-border); border-radius: 4px; }

.ch-panel__header {
    padding: 0 0.875rem;
    height: var(--ch-header-h);
    display: flex; align-items: center; gap: 0.4rem;
    border-bottom: 1px solid var(--ch-border);
    font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--ch-muted);
    flex-shrink: 0;
}
.ch-panel__header span.count {
    margin-left: auto;
    background: var(--ch-accent-glow);
    color: var(--ch-accent);
    padding: 1px 7px; border-radius: 999px;
    font-size: 0.68rem;
}

.ch-panel__section-title {
    padding: 0.6rem 0.875rem 0.25rem;
    font-size: 0.65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: var(--ch-muted); opacity: 0.7;
}

/* Usuario online en panel */
.ch-online-item {
    display: flex; align-items: center; gap: 0.55rem;
    padding: 0.38rem 0.875rem;
    border-radius: 6px; margin: 0 0.4rem;
    cursor: pointer; transition: background 0.15s;
}
.ch-online-item:hover { background: var(--theme-hover); }
.ch-online-item__avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: var(--ch-accent-glow);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 700; color: var(--ch-accent);
    overflow: hidden; flex-shrink: 0; position: relative;
}
.ch-online-item__avatar img { width: 100%; height: 100%; object-fit: cover; }
.ch-online-item__status {
    position: absolute; bottom: 0; right: 0;
    width: 8px; height: 8px; border-radius: 50%;
    border: 1.5px solid var(--ch-surface);
    background: #6b7280;
}
.ch-online-item__status.is-online { background: var(--ch-online); }
.ch-online-item__name {
    font-size: 0.8rem; font-weight: 500;
    color: var(--ch-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    flex: 1;
}

/* ── CARD UPGRADE PREMIUM ── */
.ch-upgrade-card {
    margin: auto 0.75rem 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg,
        rgba(245,158,11,0.12) 0%,
        rgba(239,68,68,0.08) 100%);
    border: 1px solid rgba(245,158,11,0.3);
    border-radius: 12px;
    text-align: center;
    flex-shrink: 0;
}
.ch-upgrade-card__crown {
    font-size: 1.5rem;
    margin-bottom: 0.35rem;
    filter: drop-shadow(0 0 8px rgba(245,158,11,0.5));
}
.ch-upgrade-card__title {
    font-size: 0.82rem; font-weight: 700;
    color: var(--ch-text); margin-bottom: 0.25rem;
}
.ch-upgrade-card__sub {
    font-size: 0.72rem; color: var(--ch-muted);
    line-height: 1.5; margin-bottom: 0.75rem;
}
.ch-upgrade-card__perks {
    display: flex; flex-direction: column; gap: 0.3rem;
    margin-bottom: 0.75rem; text-align: left;
}
.ch-upgrade-card__perk {
    font-size: 0.72rem; color: var(--ch-text);
    display: flex; align-items: center; gap: 0.4rem;
}
.ch-upgrade-card__perk i { color: var(--ch-gold); font-size: 0.65rem; }
.ch-upgrade-card__btn {
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    padding: 0.55rem 1rem;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: #fff !important; border-radius: 8px;
    font-weight: 700; font-size: 0.8rem;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.15s;
    box-shadow: 0 3px 12px rgba(245,158,11,0.35);
}
.ch-upgrade-card__btn:hover { opacity: 0.9; transform: translateY(-1px); }

/* ══ MOBILE ══ */
@media (max-width: 767px) {
    .ch-wrap {
        grid-template-columns: 1fr;
    }
    .ch-sidebar {
        position: absolute; left: 0; top: 0; bottom: 0;
        width: 80vw; z-index: 20;
        transform: translateX(-100%);
        transition: transform 0.28s cubic-bezier(.4,0,.2,1);
    }
    .ch-sidebar.is-open { transform: translateX(0); }
    .ch-panel { display: none; }
    .ch-header__back { display: flex; }
    .ch-bubble-wrap { max-width: 88%; }
}

/* ══ TABLET ══ */
@media (min-width: 768px) and (max-width: 1023px) {
    :root {
        --ch-sidebar-w: 220px;
        --ch-right-w: 0px;
    }
    .ch-panel { display: none; }
}

/* ══ Utilidades ══ */
.ch-hidden { display: none !important; }
</style>
@endpush

@push('styles')
<style>
/* ══════════════════════════════════════════════
   LOBBY69 — Chat Split-View
   Fase B · Redesign Completo
══════════════════════════════════════════════ */

/* ── Variables locales ── */
:root {
    --chat-accent:        #8b5cf6;
    --chat-accent-2:      #4f46e5;
    --chat-accent-glow:   rgba(139, 92, 246, 0.18);
    --chat-sidebar-bg:    var(--theme-surface);
    --chat-main-bg:       var(--theme-bg);
    --chat-border:        rgba(139, 92, 246, 0.15);
    --chat-bubble-own:    linear-gradient(135deg, #7c3aed, #4f46e5);
    --chat-bubble-other:  rgba(0,0,0,0.06);
    --chat-text:          var(--theme-text);
    --chat-muted:         var(--theme-muted);
    --chat-online:        #22c55e;
    --chat-sidebar-w:     320px;
    --chat-header-h:      60px;
    --chat-input-h:       70px;
    --chat-radius:        16px;
    --chat-radius-sm:     10px;
}


/* -- Wrapper global -- */
.l69-chat-wrap {
    display: grid;
    grid-template-columns: 320px 1fr 240px;
    grid-template-rows: 1fr;
    position: fixed;
    top: var(--nav-h, 64px);
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: calc(100vh - var(--nav-h, 64px));
    overflow: hidden;
    background: var(--chat-main-bg, #1a1a2e);
    z-index: 50;
    box-sizing: border-box;
}
   PANEL IZQUIERDO — Sidebar
══════════════════════════════════════════════ */
.l69-chat-sidebar {
    width: var(--chat-sidebar-w);
    min-width: var(--chat-sidebar-w);
    max-width: var(--chat-sidebar-w);
    display: flex;
    flex-direction: column;
    background: var(--theme-surface);
    border-right: 1px solid var(--chat-border);
    overflow: hidden;
    transition: transform 0.28s cubic-bezier(.4,0,.2,1);
}

/* Header del sidebar */
.l69-sidebar-header {
    padding: 1rem 1rem 0;
    flex-shrink: 0;
}

.l69-sidebar-header__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.85rem;
}

.l69-sidebar-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--chat-text);
    letter-spacing: -0.02em;
    margin: 0;
}

/* Tabs de navegación */
.l69-chat-tabs {
    display: flex;
    gap: 4px;
    background: rgba(255,255,255,0.04);
    border-radius: var(--chat-radius-sm);
    padding: 4px;
    margin-bottom: 0.75rem;
}

.l69-chat-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px 6px;
    border-radius: 7px;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--chat-muted);
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
    white-space: nowrap;
}

.l69-chat-tab:hover {
    color: var(--chat-text);
    background: rgba(255,255,255,0.06);
    text-decoration: none;
}

.l69-chat-tab.is-active {
    background: linear-gradient(135deg, var(--chat-accent), var(--chat-accent-2));
    color: #fff;
    box-shadow: 0 2px 10px rgba(139,92,246,0.4);
}

.l69-chat-tab__badge {
    position: absolute;
    top: 2px;
    right: 2px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: #ef4444;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

/* Buscador */
.l69-chat-search {
    position: relative;
    margin-bottom: 0.5rem;
}

.l69-chat-search__input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    border-radius: var(--chat-radius-sm);
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.05);
    color: var(--chat-text);
    font-size: 0.82rem;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
    box-sizing: border-box;
}

.l69-chat-search__input::placeholder { color: var(--chat-muted); }
.l69-chat-search__input:focus {
    border-color: var(--chat-accent);
    background: rgba(139,92,246,0.07);
}

.l69-chat-search__icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--chat-muted);
    font-size: 0.75rem;
    pointer-events: none;
}

/* Body scrollable del sidebar */
.l69-sidebar-body {
    flex: 1;
    overflow-y: auto;
    padding: 0.25rem 0;
}

.l69-sidebar-body::-webkit-scrollbar { width: 3px; }
.l69-sidebar-body::-webkit-scrollbar-track { background: transparent; }
.l69-sidebar-body::-webkit-scrollbar-thumb {
    background: rgba(139,92,246,0.3);
    border-radius: 2px;
}

/* ── Fila de conversación ── */
.l69-chat-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    cursor: pointer;
    border-radius: var(--chat-radius-sm);
    margin: 2px 6px;
    transition: background 0.18s ease;
    position: relative;
}

.l69-chat-row:hover {
    background: rgba(139,92,246,0.1);
}

.l69-chat-row.is-active {
    background: linear-gradient(135deg,
        rgba(139,92,246,0.2),
        rgba(79,70,229,0.15));
    border-left: 3px solid var(--chat-accent);
    padding-left: 9px;
}

.l69-chat-row.is-unread .l69-chat-row__name {
    font-weight: 700;
    color: var(--chat-text);
}

.l69-chat-row.is-unread .l69-chat-row__preview {
    color: var(--chat-text);
    font-weight: 500;
}

/* Avatar con indicador online */
.l69-chat-row__avatar {
    position: relative;
    flex-shrink: 0;
    width: 42px;
    height: 42px;
}

.l69-chat-row__avatar img,
.l69-chat-row__initials {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
}

.l69-chat-row__initials {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--chat-accent), var(--chat-accent-2));
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
}

.l69-chat-row__dot {
    position: absolute;
    bottom: 1px;
    right: 1px;
    width: 11px;
    height: 11px;
    background: var(--chat-online);
    border-radius: 50%;
    border: 2px solid var(--theme-bg, #16162e);
    animation: l69-pulse-online 2.5s ease-in-out infinite;
}


/* Info de la fila */
.l69-chat-row__info {
    flex: 1;
    min-width: 0;
}

.l69-chat-row__top {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 4px;
    margin-bottom: 2px;
}

.l69-chat-row__name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--chat-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.l69-chat-row__time {
    font-size: 0.68rem;
    color: var(--chat-muted);
    white-space: nowrap;
    flex-shrink: 0;
}

.l69-chat-row__preview {
    font-size: 0.78rem;
    color: var(--chat-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.l69-chat-row__count {
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    background: var(--chat-accent);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── Estado vacío en sidebar ── */
.l69-sidebar-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 2.5rem 1.25rem;
    gap: 0.6rem;
    color: var(--chat-muted);
}

.l69-sidebar-empty__icon {
    font-size: 2.5rem;
    opacity: 0.35;
    margin-bottom: 0.25rem;
}

.l69-sidebar-empty__title {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--chat-text);
    opacity: 0.7;
    margin: 0;
}

.l69-sidebar-empty__sub {
    font-size: 0.78rem;
    color: var(--chat-muted);
    margin: 0;
    line-height: 1.5;
}

.l69-sidebar-empty__cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: linear-gradient(135deg, var(--chat-accent), var(--chat-accent-2));
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 20px;
    text-decoration: none;
    margin-top: 0.5rem;
    transition: opacity 0.2s, transform 0.2s;
    box-shadow: 0 4px 14px rgba(139,92,246,0.35);
}

.l69-sidebar-empty__cta:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    text-decoration: none;
    color: #fff;
}

/* ── Filas de amigos ── */
.l69-friend-section-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--chat-muted);
    padding: 10px 16px 4px;
}

.l69-friend-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    margin: 2px 6px;
    border-radius: var(--chat-radius-sm);
    transition: background 0.18s;
}

.l69-friend-row:hover { background: rgba(255,255,255,0.04); }

.l69-friend-row__info {
    flex: 1;
    min-width: 0;
}

.l69-friend-row__name {
    font-size: 0.83rem;
    font-weight: 600;
    color: var(--chat-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.l69-friend-row__meta {
    font-size: 0.72rem;
    color: var(--chat-muted);
}

.l69-friend-row__actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.l69-btn-xs {
    padding: 4px 10px;
    font-size: 0.68rem;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: opacity 0.18s, transform 0.18s;
}
.l69-btn-xs:hover { opacity: 0.85; transform: translateY(-1px); }
.l69-btn-xs--accept { background: #22c55e; color: #fff; }
.l69-btn-xs--reject { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
.l69-btn-xs--chat   { background: var(--chat-accent-glow); color: var(--chat-accent); border: 1px solid var(--chat-border); }

/* ── Filas de anuncios ── */
.l69-ann-row {
    display: flex;
    gap: 10px;
    padding: 10px 12px;
    margin: 2px 6px;
    border-radius: var(--chat-radius-sm);
    cursor: pointer;
    transition: background 0.18s;
    border: 1px solid transparent;
}

.l69-ann-row:hover {
    background: rgba(139,92,246,0.08);
    border-color: var(--chat-border);
}

.l69-ann-row.is-active {
    background: rgba(139,92,246,0.15);
    border-color: rgba(139,92,246,0.3);
}

.l69-ann-row__info { flex: 1; min-width: 0; }

.l69-ann-row__title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--chat-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.l69-ann-row__meta { font-size: 0.72rem; color: var(--chat-muted); }

.l69-ann-badge {
    display: inline-block;
    padding: 2px 7px;
    font-size: 0.62rem;
    font-weight: 700;
    border-radius: 4px;
    background: rgba(139,92,246,0.15);
    color: var(--chat-accent);
    margin-right: 3px;
}

.l69-ann-badge--expired {
    background: rgba(239,68,68,0.12);
    color: #ef4444;
}

/* ══════════════════════════════════════════════
   PANEL DERECHO — Main Chat Area
══════════════════════════════════════════════ */
.l69-chat-main {
    min-width: 0;
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    background: var(--theme-bg);
    position: relative;
}

/* Placeholder inicial */
.l69-chat-placeholder {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    color: var(--chat-muted);
    padding: 2rem;
    text-align: center;
}

/* Área de chat activo */
#chatActive {
    display: none;
    flex-direction: column;
    height: 100%;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

#chatActive.is-visible {
    display: flex;
}

.l69-chat-placeholder__icon {
    font-size: 3.5rem;
    opacity: 0.15;
    margin-bottom: 0.5rem;
}

.l69-chat-placeholder__title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--chat-text);
    opacity: 0.5;
    margin: 0;
}

.l69-chat-placeholder__sub {
    font-size: 0.82rem;
    color: var(--chat-muted);
    opacity: 0.7;
    margin: 0;
    max-width: 280px;
    line-height: 1.6;
}

/* ── Header del chat activo ── */
.l69-chat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 1.25rem;
    height: var(--chat-header-h);
    min-height: var(--chat-header-h);
    border-bottom: 1px solid var(--chat-border);
    background: linear-gradient(90deg,
        rgba(139,92,246,0.07) 0%,
        transparent 60%);
    flex-shrink: 0;
}

.l69-chat-header__back {
    display: none;
    background: none;
    border: none;
    color: var(--chat-muted);
    cursor: pointer;
    font-size: 1rem;
    padding: 4px 8px;
    border-radius: 6px;
    transition: color 0.2s;
}

.l69-chat-header__back:hover { color: var(--chat-text); }

.l69-chat-header__avatar {
    position: relative;
    width: 38px;
    height: 38px;
    flex-shrink: 0;
}

.l69-chat-header__avatar img,
.l69-chat-header__avatar-initials {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
}

.l69-chat-header__avatar-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--chat-accent), var(--chat-accent-2));
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
}

.l69-chat-header__info { flex: 1; min-width: 0; }

.l69-chat-header__name {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--chat-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
    line-height: 1.3;
}

.l69-chat-header__status {
    font-size: 0.72rem;
    color: var(--chat-online);
    margin: 0;
}

.l69-chat-header__status--typing {
    color: var(--chat-accent);
    font-style: italic;
}

.l69-chat-header__actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.l69-chat-header__btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--chat-border);
    background: rgba(255,255,255,0.04);
    color: var(--chat-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    transition: all 0.2s;
    text-decoration: none;
}

.l69-chat-header__btn:hover {
    background: var(--chat-accent-glow);
    color: var(--chat-accent);
    border-color: rgba(139,92,246,0.35);
    text-decoration: none;
}

/* ── Área de mensajes ── */
.l69-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 4px;
    scroll-behavior: smooth;
}

.l69-chat-messages::-webkit-scrollbar { width: 4px; }
.l69-chat-messages::-webkit-scrollbar-track { background: transparent; }
.l69-chat-messages::-webkit-scrollbar-thumb {
    background: rgba(139,92,246,0.25);
    border-radius: 2px;
}

/* Separador de fecha */
.l69-msg-date-sep {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0.75rem 0;
    color: var(--chat-muted);
    font-size: 0.7rem;
}

.l69-msg-date-sep::before,
.l69-msg-date-sep::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--chat-border);
}

/* Burbujas */
.l69-bubble-wrap {
    display: flex;
    align-items: flex-end;
    gap: 7px;
    max-width: 72%;
    animation: l69-bubble-in 0.2s ease;
}

@keyframes l69-bubble-in {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.l69-bubble-wrap--own {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.l69-bubble-wrap--other {
    align-self: flex-start;
}

.l69-bubble-wrap + .l69-bubble-wrap--own,
.l69-bubble-wrap + .l69-bubble-wrap--other {
    margin-top: 2px;
}

.l69-bubble-wrap--own + .l69-bubble-wrap--other,
.l69-bubble-wrap--other + .l69-bubble-wrap--own {
    margin-top: 10px;
}

.l69-bubble {
    padding: 9px 14px;
    font-size: 0.85rem;
    line-height: 1.55;
    word-break: break-word;
    position: relative;
    max-width: 100%;
}

.l69-bubble--own {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: #fff;
    border-radius: 18px 18px 4px 18px;
    box-shadow: 0 2px 12px rgba(124,58,237,0.35);
}

.l69-bubble--other {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--chat-text);
    border-radius: 18px 18px 18px 4px;
}

.l69-bubble__time {
    display: block;
    font-size: 0.62rem;
    opacity: 0.6;
    margin-top: 4px;
    text-align: right;
}

.l69-bubble--other .l69-bubble__time {
    text-align: left;
}

/* Mini avatar en burbuja (otros) */
.l69-bubble-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    margin-bottom: 2px;
    opacity: 0.85;
}

/* Indicador de escritura */
.l69-typing-indicator {
    display: none;
    align-items: center;
    gap: 6px;
    padding: 4px 0;
    align-self: flex-start;
}

.l69-typing-indicator.is-visible { display: flex; }

.l69-typing-dots {
    display: flex;
    gap: 3px;
    padding: 8px 12px;
    background: rgba(255,255,255,0.07);
    border-radius: 12px 12px 12px 4px;
}

.l69-typing-dots span {
    width: 5px;
    height: 5px;
    background: var(--chat-muted);
    border-radius: 50%;
    animation: l69-typing 1.2s ease-in-out infinite;
}

.l69-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.l69-typing-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes l69-typing {
    0%,60%,100% { transform: translateY(0); opacity: 0.4; }
    30%          { transform: translateY(-4px); opacity: 1; }
}

/* Loader de mensajes */
.l69-chat-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--chat-muted);
    font-size: 0.82rem;
    gap: 8px;
}

.l69-chat-loader i { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Input de mensaje ── */
.l69-chat-input-area {
    padding: 10px 1rem;
    border-top: 1px solid var(--chat-border);
    background: rgba(0,0,0,0.15);
    flex-shrink: 0;
}

.l69-chat-input-wrap {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 24px;
    padding: 8px 8px 8px 16px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.l69-chat-input-wrap:focus-within {
    border-color: var(--chat-accent);
    box-shadow: 0 0 0 3px rgba(139,92,246,0.12);
}

.l69-chat-input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: var(--chat-text);
    font-size: 0.87rem;
    line-height: 1.5;
    resize: none;
    min-height: 22px;
    max-height: 100px;
    padding: 0;
    font-family: inherit;
}

.l69-chat-input::placeholder { color: var(--chat-muted); }

.l69-chat-send-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, var(--chat-accent), var(--chat-accent-2));
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
    transition: opacity 0.2s, transform 0.18s, box-shadow 0.2s;
    box-shadow: 0 2px 10px rgba(139,92,246,0.4);
}

.l69-chat-send-btn:hover {
    opacity: 0.9;
    transform: scale(1.08);
}

.l69-chat-send-btn:disabled {
    opacity: 0.35;
    box-shadow: none;
    transform: none;
    cursor: default;
}

/* ── Panel de Anuncio detalle ── */
.l69-ann-detail {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: none;
    flex-direction: column;
    gap: 1rem;
}

.l69-ann-detail.is-visible { display: flex; }

.l69-ann-detail__header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--chat-border);
}

.l69-ann-detail__body { line-height: 1.7; }

.l69-ann-detail__title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--chat-text);
    margin: 0 0 4px;
}

.l69-ann-detail__proposal {
    font-size: 0.87rem;
    color: var(--chat-muted);
    white-space: pre-wrap;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--chat-border);
    border-radius: var(--chat-radius-sm);
    padding: 12px 14px;
}

.l69-ann-detail__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 0.5rem;
}

/* Botón CTA de anuncio */
.l69-btn-accent {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 20px;
    background: linear-gradient(135deg, var(--chat-accent), var(--chat-accent-2));
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.2s;
    box-shadow: 0 4px 14px rgba(139,92,246,0.35);
    width: fit-content;
}

.l69-btn-accent:hover {
    opacity: 0.88;
    transform: translateY(-1px);
    color: #fff;
    text-decoration: none;
}

/* ══════════════════════════════════════════════
   TOAST DE MENSAJE EN TIEMPO REAL
══════════════════════════════════════════════ */
.l69-rt-toast {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    background: linear-gradient(135deg,
        rgba(30,30,60,0.97),
        rgba(20,20,45,0.97));
    border: 1px solid rgba(139,92,246,0.35);
    border-radius: 14px;
    padding: 12px 16px;
    min-width: 230px;
    max-width: 300px;
    box-shadow: 0 8px 28px rgba(0,0,0,0.45),
                0 0 0 1px rgba(139,92,246,0.1);
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(.4,0,.2,1);
}

.l69-rt-toast--in {
    transform: translateY(0);
    opacity: 1;
}

.l69-rt-toast--out {
    transform: translateY(-10px);
    opacity: 0;
}

.l69-rt-toast__nick {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--chat-accent);
    margin-bottom: 3px;
}

.l69-rt-toast__body {
    font-size: 0.82rem;
    color: #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ══════════════════════════════════════════════
   MODAL CREAR ANUNCIO
══════════════════════════════════════════════ */
.l69-ann-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(4px);
}

.l69-ann-modal.is-open { display: flex; }

.l69-ann-modal__box {
    background: var(--theme-surface, #1e1e3a);
    border: 1px solid var(--chat-border);
    border-radius: var(--chat-radius);
    width: 100%;
    max-width: 520px;
    max-height: 88vh;
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0,0,0,0.5);
}

.l69-ann-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--chat-border);
    position: sticky;
    top: 0;
    background: var(--theme-surface, #1e1e3a);
    z-index: 1;
}

.l69-ann-modal__title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--chat-text);
    margin: 0;
}

.l69-ann-modal__close {
    background: none;
    border: none;
    color: var(--chat-muted);
    cursor: pointer;
    font-size: 1.1rem;
    padding: 4px 8px;
    border-radius: 6px;
    transition: color 0.2s;
}

.l69-ann-modal__close:hover { color: var(--chat-text); }

.l69-ann-modal__body { padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }

.l69-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.l69-field label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--chat-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.l69-field input,
.l69-field textarea,
.l69-field select {
    padding: 9px 12px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--chat-radius-sm);
    color: var(--chat-text);
    font-size: 0.85rem;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
}

.l69-field input:focus,
.l69-field textarea:focus,
.l69-field select:focus {
    border-color: var(--chat-accent);
}

.l69-field textarea { resize: vertical; min-height: 80px; }

.l69-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.l69-checkbox-pill {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.04);
    cursor: pointer;
    font-size: 0.75rem;
    color: var(--chat-muted);
    transition: all 0.18s;
}

.l69-checkbox-pill:has(input:checked) {
    border-color: var(--chat-accent);
    background: var(--chat-accent-glow);
    color: var(--chat-accent);
}

.l69-checkbox-pill input { display: none; }

.l69-ann-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--chat-border);
}

.l69-btn-ghost {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--chat-border);
    border-radius: 10px;
    color: var(--chat-muted);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.l69-btn-ghost:hover {
    border-color: var(--chat-accent);
    color: var(--chat-accent);
}

/* ══════════════════════════════════════════════
   RESPONSIVE — Mobile
══════════════════════════════════════════════ */
@media (max-width: 768px) {
    .l69-chat-wrap {
        height: calc(100vh - 60px);
        border-radius: 0;
        border-left: none;
        border-right: none;
    }

    .l69-chat-sidebar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        z-index: 10;
        transform: translateX(0);
        min-width: 100vw;
        max-width: 100vw;
    }

    .l69-chat-sidebar.is-hidden {
        transform: translateX(-100%);
    }

    .l69-chat-main {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: translateX(100%);
        transition: transform 0.28s cubic-bezier(.4,0,.2,1);
    }

    .l69-chat-main.is-open {
        transform: translateX(0);
    }

    .l69-chat-header__back { display: flex; }

    .l69-bubble-wrap { max-width: 88%; }
}

/* Utilidad ocultar */


/* ══════════════════════════════════════════
   MEMBERSHIP GATE — Fricción progresiva
   ══════════════════════════════════════════ */
.l69-chat-gate {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.25rem;
    border-radius: var(--chat-radius-sm);
    margin: 0.5rem 1rem;
    font-size: 0.875rem;
    animation: l69-gate-in 0.3s ease;
}

@keyframes l69-gate-in {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.l69-chat-gate--locked {
    background: linear-gradient(135deg, rgba(139,92,246,0.12), rgba(79,70,229,0.08));
    border: 1px solid rgba(139,92,246,0.25);
}

.l69-chat-gate--limit {
    background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(239,68,68,0.08));
    border: 1px solid rgba(245,158,11,0.25);
}

.l69-chat-gate--warn {
    background: rgba(239,68,68,0.08);
    border: 1px solid rgba(239,68,68,0.2);
}

.l69-chat-gate__icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.l69-chat-gate__text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.l69-chat-gate__text strong {
    color: var(--chat-text);
    font-weight: 700;
}

.l69-chat-gate__text span {
    color: var(--chat-muted);
    font-size: 0.8rem;
}

.l69-chat-gate__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: #fff !important;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: opacity 0.2s;
    flex-shrink: 0;
}

.l69-chat-gate__btn:hover { opacity: 0.85; }

.l69-chat-gate__btn--gold {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
}

/* ── Contador de mensajes restantes ── */
.l69-chat-counter {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 1.25rem;
    font-size: 0.78rem;
    color: var(--chat-muted);
    border-bottom: 1px solid var(--chat-border);
}

.l69-chat-counter i { color: var(--chat-accent); }

#chatCounterNum {
    font-weight: 700;
    color: var(--chat-accent);
}

.l69-chat-counter__upgrade {
    margin-left: auto;
    color: #f59e0b;
    font-weight: 600;
    font-size: 0.78rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    animation: l69-pulse-gold 1.5s ease-in-out infinite;
}

@keyframes l69-pulse-gold {
    0%,100% { opacity: 1; }
    50%      { opacity: 0.6; }
}

   OVERRIDE — /mensajes ocupa pantalla completa
   ══════════════════════════════════════════ */

/* Eliminar TODOS los espacios del layout en página de mensajes */
body.page-mensajes,
body.page-mensajes .l69-layout,
body.page-mensajes .l69-layout__content,
body.page-mensajes main,
body.page-mensajes > main,
body.page-mensajes .l69-main {
    display: block !important;
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    gap: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    min-height: 0 !important;
    overflow: hidden !important;
}
body.page-mensajes .l69-sidebar--left,
body.page-mensajes .l69-sidebar--right {
    display: none !important;
}
   PANEL DERECHO — Usuarios Online
══════════════════════════════════════════════ */
.l69-users-panel {
    display: flex;
    flex-direction: column;
    background: var(--theme-surface);
    border-left: 1px solid var(--chat-border);
    overflow-y: auto;
    overflow-x: hidden;
    height: 100%;
}

.l69-users-panel__header {
    padding: 1rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--chat-muted);
    border-bottom: 1px solid var(--chat-border);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.l69-users-panel__section-title {
    padding: 0.6rem 1rem 0.3rem;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--chat-muted);
    opacity: 0.7;
}

.l69-users-panel__list {
    list-style: none;
    margin: 0;
    padding: 0 0 0.5rem;
}

.l69-users-panel__item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.45rem 1rem;
    cursor: pointer;
    border-radius: 6px;
    margin: 0 0.4rem;
    transition: background 0.15s;
}

.l69-users-panel__item:hover {
    background: rgba(139,92,246,0.08);
}

.l69-users-panel__avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    background: var(--chat-border);
}

.l69-users-panel__initials {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--chat-accent), var(--chat-accent-2));
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.l69-users-panel__name {
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--chat-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.l69-users-panel__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.l69-users-panel__dot--online {
    background: #22c55e;
    box-shadow: 0 0 0 2px rgba(34,197,94,0.25);
    animation: l69-pulse-online 2s ease-in-out infinite;
}

.l69-users-panel__dot--offline {
    background: var(--chat-muted);
    opacity: 0.4;
}

.l69-users-panel__empty {
    padding: 0.5rem 1rem;
    font-size: 0.72rem;
    color: var(--chat-muted);
    opacity: 0.6;
}

.l69-users-panel__sala {
    margin: auto 0.75rem 0.75rem;
    padding: 0.6rem 0.8rem;
    background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(167,139,250,0.1));
    border: 1px solid rgba(139,92,246,0.25);
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--chat-accent);
    transition: background 0.2s;
    text-decoration: none;
    display: block;
}

.l69-users-panel__sala:hover {
    background: linear-gradient(135deg, rgba(139,92,246,0.25), rgba(167,139,250,0.18));
}



/* ══════════════════════════════════════════════
   SALA GENERAL — Panel integrado en columna central
   ══════════════════════════════════════════════ */
.l69-sala-panel {
    display: none;
    flex-direction: column;
    flex: 1;
    min-height: 0;
    height: 100%;
    width: 100%;
    background: var(--theme-bg);
    position: relative;
    overflow: hidden;
}
.l69-sala-panel.is-visible {
    display: flex;
}

/* Header sala */
.l69-sala-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.25rem;
    height: var(--chat-header-h, 60px);
    background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
    color: #fff;
    flex-shrink: 0;
    border-bottom: 1px solid rgba(255,255,255,0.12);
}
.l69-sala-header__left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.l69-sala-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.l69-sala-header__title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #fff;
}
.l69-sala-header__sub {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.75);
    margin-top: 1px;
}
.l69-sala-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4ade80;
    display: inline-block;
    box-shadow: 0 0 6px #4ade80;
    animation: l69-sala-pulse 2s infinite;
}
@keyframes l69-sala-pulse {
    0%,100% { opacity: 1; }
    50%      { opacity: 0.5; }
}
.l69-sala-header__right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.l69-sala-header .l69-chat-header__btn {
    color: rgba(255,255,255,0.85);
}
.l69-sala-header .l69-chat-header__btn:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
}

/* Gate freemium */
.l69-sala-gate {
    position: relative;
    flex-shrink: 0;
}
.l69-sala-gate__blur-preview {
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    filter: blur(4px);
    user-select: none;
    pointer-events: none;
}
.l69-sala-gate__msg {
    background: var(--chat-bubble-other, rgba(0,0,0,0.08));
    padding: 0.5rem 0.85rem;
    border-radius: 12px;
    font-size: 0.875rem;
    max-width: 65%;
    color: var(--chat-text);
}
.l69-sala-gate__msg--r {
    align-self: flex-end;
    background: var(--chat-bubble-own, linear-gradient(135deg,#7c3aed,#4f46e5));
    color: #fff;
}
.l69-sala-gate__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom,
        rgba(26,26,46,0.7) 0%,
        rgba(26,26,46,0.95) 60%,
        rgba(26,26,46,1) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    text-align: center;
    gap: 0.5rem;
}
.l69-sala-gate__lock {
    font-size: 2rem;
    margin-bottom: 0.25rem;
}
.l69-sala-gate__title {
    font-weight: 700;
    font-size: 1rem;
    color: var(--chat-text);
}
.l69-sala-gate__sub {
    font-size: 0.8rem;
    color: var(--chat-muted);
    max-width: 280px;
    line-height: 1.5;
}
.l69-sala-gate__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1.25rem;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: #fff !important;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.85rem;
    text-decoration: none;
    margin-top: 0.5rem;
    transition: opacity 0.2s, transform 0.2s;
    box-shadow: 0 4px 14px rgba(245,158,11,0.35);
}
.l69-sala-gate__cta:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
.l69-sala-gate__cta--sm {
    padding: 0.4rem 0.9rem;
    font-size: 0.78rem;
    margin-top: 0;
}

/* Mensajes sala */
.l69-sala-msgs {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    scroll-behavior: smooth;
}
.l69-sala-bubble {
    display: flex;
    flex-direction: column;
    max-width: 72%;
    align-self: flex-start;
    animation: l69-bubble-in 0.2s ease;
}
@keyframes l69-bubble-in {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.l69-sala-bubble--self {
    align-self: flex-end;
    text-align: right;
}
.l69-sala-author {
    font-size: 0.68rem;
    color: var(--chat-muted);
    margin-bottom: 3px;
    padding: 0 0.5rem;
}
.l69-sala-text {
    background: var(--theme-surface, rgba(0,0,0,0.08));
    color: var(--chat-text);
    padding: 0.5rem 0.875rem;
    border-radius: 0 12px 12px 12px;
    font-size: 0.875rem;
    line-height: 1.45;
    word-break: break-word;
    border: 1px solid var(--chat-border);
}
.l69-sala-bubble--self .l69-sala-text {
    background: var(--chat-bubble-own, linear-gradient(135deg,#7c3aed,#4f46e5));
    color: #fff;
    border-color: transparent;
    border-radius: 12px 12px 0 12px;
}

/* Footer sala */
.l69-sala-footer {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--chat-border);
    background: var(--theme-surface);
    flex-shrink: 0;
    align-items: center;
}
.l69-sala-footer--locked {
    justify-content: space-between;
    background: rgba(139,92,246,0.06);
}
.l69-sala-footer__locked-msg {
    font-size: 0.82rem;
    color: var(--chat-muted);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.l69-sala-input {
    flex: 1;
    background: var(--theme-bg);
    border: 1px solid var(--chat-border);
    border-radius: 10px;
    color: var(--chat-text);
    padding: 0.55rem 0.875rem;
    font-size: 0.875rem;
    outline: none;
    transition: border-color 0.2s;
}
.l69-sala-input:focus {
    border-color: var(--chat-accent);
    box-shadow: 0 0 0 3px var(--chat-accent-glow);
}
.l69-sala-send {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    border: none;
    border-radius: 10px;
    color: #fff;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.2s, transform 0.15s;
    flex-shrink: 0;
}
.l69-sala-send:hover {
    opacity: 0.88;
    transform: scale(1.05);
}

/* ══ PLACEHOLDER rediseñado ══ */
.l69-chat-placeholder {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.l69-ph-glow {
    position: absolute;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(139,92,246,0.12) 0%, transparent 70%);
    pointer-events: none;
}
.l69-ph-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    filter: drop-shadow(0 0 16px rgba(139,92,246,0.4));
    animation: l69-ph-float 3s ease-in-out infinite;
}
@keyframes l69-ph-float {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-6px); }
}
.l69-ph-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--chat-text);
    margin-bottom: 0.5rem;
}
.l69-ph-sub {
    font-size: 0.85rem;
    color: var(--chat-muted);
    line-height: 1.6;
    margin-bottom: 1.75rem;
}
.l69-ph-actions {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    width: 100%;
    max-width: 220px;
    margin-bottom: 2rem;
}
.l69-ph-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 1.25rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.15s;
}
.l69-ph-btn:hover {
    opacity: 0.88;
    transform: translateY(-1px);
}
.l69-ph-btn--sala {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: #fff;
    box-shadow: 0 4px 14px rgba(124,58,237,0.3);
}
.l69-ph-btn--explore {
    background: var(--theme-surface);
    color: var(--chat-text);
    border: 1px solid var(--chat-border);
}
.l69-ph-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1.5rem;
    background: var(--theme-surface);
    border: 1px solid var(--chat-border);
    border-radius: 12px;
}
.l69-ph-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
}
.l69-ph-stat__num {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--chat-accent);
}
.l69-ph-stat__label {
    font-size: 0.7rem;
    color: var(--chat-muted);
    white-space: nowrap;
}
.l69-ph-stat__sep {
    width: 1px;
    height: 28px;
    background: var(--chat-border);
}

/* ══ BOTÓN SALA en panel derecho ══ */
.l69-users-panel__sala {
    margin: auto 0.75rem 0.75rem;
    padding: 0.65rem 1rem;
    background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(79,70,229,0.1));
    border: 1px solid rgba(124,58,237,0.3);
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--chat-accent);
    transition: all 0.2s;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.l69-users-panel__sala::before {
    content: "💬";
    font-size: 1rem;
}
.l69-users-panel__sala:hover {
    background: linear-gradient(135deg, rgba(124,58,237,0.25), rgba(79,70,229,0.18));
    border-color: var(--chat-accent);
    transform: translateY(-1px);
}
.l69-users-panel__sala.is-active {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 14px rgba(124,58,237,0.35);
}

/* ═══════════════════════════════════════════════════════
   VIDEO CALL — Modales y controles  LOBBY69
═══════════════════════════════════════════════════════ */
.vc-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: #000;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.vc-modal.hidden { display: none; }
.vc-videos {
    position: relative;
    width: 100%;
    flex: 1;
    background: #111;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.vc-remote {
    width: 100%;
    height: 100%;
    max-height: calc(100vh - 130px);
    object-fit: cover;
    background: #1a1a2e;
}
.vc-local {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 140px;
    height: 105px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #e91e8c;
    box-shadow: 0 4px 20px rgba(0,0,0,.6);
    z-index: 2;
}
.vc-remote-name {
    position: absolute;
    top: 14px;
    left: 16px;
    color: #fff;
    font-weight: 600;
    font-size: .95rem;
    text-shadow: 0 1px 4px rgba(0,0,0,.8);
    z-index: 2;
    pointer-events: none;
}
.vc-status-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    background: rgba(0,0,0,.45);
    pointer-events: none;
    transition: opacity .4s;
}
.vc-status-overlay:empty { opacity: 0; }
.vc-timer-wrap {
    width: 100%;
    padding: 8px 16px 4px;
    background: #0d0d1a;
    display: flex;
    align-items: center;
    gap: 12px;
}
.vc-timer-wrap.hidden { display: none; }
.vc-bar-bg {
    flex: 1;
    height: 6px;
    background: #2a2a3e;
    border-radius: 3px;
    overflow: hidden;
}
.vc-bar {
    height: 100%;
    background: linear-gradient(90deg,#e91e8c,#9c27b0);
    border-radius: 3px;
    transition: width .9s linear, background .3s;
    width: 100%;
}
.vc-bar.warning { background: linear-gradient(90deg,#ff6b35,#f44336); }
.vc-timer {
    font-family: monospace;
    font-size: 1rem;
    color: #e0e0e0;
    min-width: 52px;
    text-align: right;
}
.vc-timer.warning { color: #ff6b35; font-weight: 700; }
.vc-controls {
    width: 100%;
    padding: 10px 16px 14px;
    background: #0d0d1a;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
}
.vc-btn {
    border: none;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
    cursor: pointer;
    transition: transform .15s, opacity .15s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.vc-btn:hover { transform: scale(1.1); }
.vc-btn-mute   { background: #2a2a3e; color: #fff; }
.vc-btn-cam    { background: #2a2a3e; color: #fff; }
.vc-btn-hangup {
    background: #f44336;
    color: #fff;
    width: 56px;
    height: 56px;
    font-size: 1.4rem;
}
.vc-inline-toast {
    position: absolute;
    bottom: 140px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255,107,53,.93);
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: .88rem;
    text-align: center;
    z-index: 10;
    pointer-events: none;
    animation: vcToastIn .3s ease;
}
@keyframes vcToastIn {
    from { opacity:0; transform:translateX(-50%) translateY(10px); }
    to   { opacity:1; transform:translateX(-50%) translateY(0); }
}
.vc-incoming {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9998;
    animation: vcSlideIn .35s cubic-bezier(.34,1.56,.64,1);
}
.vc-incoming.hidden { display: none; }
@keyframes vcSlideIn {
    from { opacity:0; transform:translateY(30px) scale(.9); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
.vc-incoming-card {
    background: linear-gradient(135deg,#1a1a2e,#16213e);
    border: 1px solid #e91e8c44;
    border-radius: 16px;
    padding: 20px 24px;
    min-width: 280px;
    box-shadow: 0 8px 32px rgba(233,30,140,.25);
}
.vc-incoming-ring {
    font-size: 2rem;
    text-align: center;
    margin-bottom: 8px;
    animation: vcRing .6s ease infinite alternate;
    display: block;
}
@keyframes vcRing {
    from { transform: rotate(-15deg); }
    to   { transform: rotate(15deg); }
}
.vc-incoming-title {
    color: #aaa;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin: 0 0 4px;
    text-align: center;
}
.vc-incoming-name {
    color: #fff;
    font-size: 1.15rem;
    font-weight: 700;
    text-align: center;
    margin: 0 0 16px;
}
.vc-incoming-actions {
    display: flex;
    gap: 12px;
}
.vc-btn-accept {
    flex: 1;
    padding: 10px;
    background: linear-gradient(135deg,#4caf50,#2e7d32);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s;
}
.vc-btn-reject {
    flex: 1;
    padding: 10px;
    background: linear-gradient(135deg,#f44336,#b71c1c);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s;
}
.vc-btn-accept:hover,
.vc-btn-reject:hover { opacity: .85; }
.vc-trigger-btn {
    background: none;
    border: 1px solid #e91e8c55;
    color: #e91e8c;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: .82rem;
    cursor: pointer;
    transition: all .2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    line-height: 1;
}
.vc-trigger-btn:hover {
    background: #e91e8c22;
    border-color: #e91e8c;
}
</style>
@endpush


@section('content')
@php
    $userId    = (string) Auth::id();
    $u         = Auth::user();
    $mt        = $u ? $u->membership_type : null;
    $esMiembro = $mt && $mt !== 'free';
@endphp

<div class="ch-wrap" id="chWrap">

    {{-- PANEL IZQUIERDO — Canales + DMs --}}
    <aside class="ch-sidebar" id="chSidebar">

        <div class="ch-sidebar__header">
            <span class="ch-sidebar__title">💬 Mensajes</span>
        </div>

        <div class="ch-search">
            <div class="ch-search__inner">
                <i class="fas fa-search"></i>
                <input type="text" id="chSearchInput" class="ch-search__input"
                       placeholder="Buscar conversación...">
            </div>
        </div>

        <div class="ch-section">
            <div class="ch-section__label">
                <span>Canales</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <a href="#" class="ch-channel is-active" id="btnSalaGeneral">
                <span class="ch-channel__hash">💬</span>
                <span class="ch-channel__name">general</span>
                <span class="ch-channel__dot" id="salaActiveDot"></span>
            </a>
        </div>

        <div class="ch-section">
            <div class="ch-section__label">
                <span>Mensajes directos</span>
            </div>
        </div>

        <div class="ch-dm-list" id="chDmList">
            @forelse($conversations as $c)
            <div class="ch-dm"
                 data-partner="{{ $c->partner_id }}"
                 data-name="{{ e($c->nickname ?? $c->display_name) }}"
                 data-avatar="{{ $c->avatar_photo_id ?? '' }}"
                 data-verified="{{ $c->verified_profile ? '1' : '0' }}"
                 id="dm-{{ $c->partner_id }}">
                <div class="ch-dm__avatar">
                    @if($c->avatar_photo_id)
                        <img src="{{ route('photos.serve', $c->avatar_photo_id) }}"
                             alt="{{ $c->nickname ?? $c->display_name }}" loading="lazy">
                    @else
                        {{ mb_strtoupper(mb_substr($c->nickname ?? $c->display_name ?? '?', 0, 1)) }}
                    @endif
                    <span class="ch-dm__online-dot" id="dot-{{ $c->partner_id }}"></span>
                </div>
                <div class="ch-dm__info">
                    <div class="ch-dm__name">
                        {{ $c->nickname ?? $c->display_name }}
                        @if($c->verified_profile)
                            <i class="fas fa-check-circle" style="color:#27ae60;font-size:.6rem;"></i>
                        @endif
                    </div>
                    <div class="ch-dm__preview">
                        @if((string)$c->sender_id === $userId)
                            <span style="opacity:.7">Tu: </span>
                        @endif
                        {{ \Illuminate\Support\Str::limit($c->last_message, 36) }}
                    </div>
                </div>
                <div class="ch-dm__meta">
                    <span class="ch-dm__time">
                        {{ \Carbon\Carbon::parse($c->last_at)->diffForHumans(null, true) }}
                    </span>
                    @if($c->unread_count > 0)
                        <span class="ch-dm__count">{{ $c->unread_count }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="ch-dm-empty">
                <div class="ch-dm-empty__icon">💌</div>
                <p>Sin conversaciones aun</p>
                <a href="{{ route('explore') }}">Explorar perfiles</a>
            </div>
            @endforelse
        </div>

    </aside>

    {{-- COLUMNA CENTRAL --}}
    <main class="ch-main" id="chMain">

        {{-- SALA GENERAL --}}
        <div id="salaView" style="display:none;flex-direction:column;flex:1;min-height:0;overflow:hidden;height:100%;">

            <div class="ch-header">
                <button class="ch-header__btn ch-header__back" id="btnBackSala">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="ch-header__icon ch-header__icon--sala">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="ch-header__info">
                    <div class="ch-header__name">Sala General</div>
                    <div class="ch-header__sub">
                        <span class="ch-header__live-dot"></span>
                        <span id="salaCount">0</span> participantes en vivo
                    </div>
                </div>
                <div class="ch-header__actions">
                    <button class="ch-header__btn" id="salaCerrar" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="ch-messages" id="salaMsgs"></div>

            @if($esMiembro)
            <div class="ch-input-area">
                <div class="ch-input-wrap">
                    <textarea id="salaInput" class="ch-input"
                              placeholder="Escribe en #general..." rows="1"
                              maxlength="300"></textarea>
                    <button id="salaEnviar" class="ch-send-btn" title="Enviar">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            @else
            <div class="ch-gate">
                <div class="ch-gate__top">
                    <div class="ch-gate__avatar-stack">
                        <span>A</span><span>B</span><span>C</span>
                    </div>
                    <div class="ch-gate__txt">
                        <strong id="salaGateCount">0 miembros</strong> activos ahora
                    </div>
                </div>
                <a href="{{ route('membership.index') }}" class="ch-gate__cta">
                    <i class="fas fa-crown"></i>
                    Unete y participa en la conversacion
                </a>
            </div>
            @endif

        </div>

        {{-- CHAT PRIVADO --}}
        <div id="dmView" style="display:none;flex-direction:column;flex:1;min-height:0;overflow:hidden;height:100%;">

            <div class="ch-header" id="dmHeader">
                <button class="ch-header__btn ch-header__back" id="btnBackDm">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="ch-header__icon" id="dmHeaderAvatar">
                    <span id="dmHeaderInitials">?</span>
                </div>
                <div class="ch-header__info">
                    <div class="ch-header__name" id="dmHeaderName">---</div>
                    <div class="ch-header__sub" id="dmHeaderStatus"></div>
                </div>
                <div class="ch-header__actions">
                    <a href="#" id="dmHeaderProfileLink" class="ch-header__btn" title="Ver perfil">
                        <i class="fas fa-user"></i>
                    </a>
                    <button class="vc-trigger-btn" id="dmVideoCallBtn"
                            onclick="startVideoCall(window._dmCurrentUserId, window._dmCurrentUserName)"
                            title="Videollamada privada">
                        📹 <span class="d-none d-sm-inline">Video</span>
                    </button>
                </div>
            </div>

            <div class="ch-messages" id="dmMessages">
                <div class="ch-loader" id="dmLoader" style="display:none;">
                    <i class="fas fa-circle-notch"></i> Cargando...
                </div>
                <div class="ch-typing" id="typingIndicator">
                    <div class="ch-typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                    escribiendo...
                </div>
            </div>

            <div class="ch-input-area" id="dmInputArea">
                <div class="ch-input-wrap">
                    <textarea id="chatMsgInput" class="ch-input"
                              placeholder="Escribe un mensaje..." rows="1"
                              maxlength="1000"></textarea>
                    <button id="chatSendBtn" class="ch-send-btn" disabled title="Enviar">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <input type="hidden" id="chatReceiverId" value="">
            </div>

        </div>


    {{-- ═══ MODAL UPGRADE ════════════════════════════════════════════════ --}}
    <div id="upgradeOverlay" role="dialog" aria-modal="true" aria-labelledby="upgradeTitle">
        <div class="upgrade-card">
            <span class="upgrade-crown">👑</span>
            <div class="upgrade-title" id="upgradeTitle">¡Límite de mensajes alcanzado!</div>
            <div class="upgrade-subtitle">
                Has usado <strong id="upgradeUsed">5</strong> de <strong id="upgradeLimit">5</strong>
                mensajes disponibles hoy.<br>Hazte miembro y disfruta sin límites.
            </div>
            <div class="upgrade-progress">
                <div class="upgrade-progress__bar" id="upgradeProgressBar" style="width:100%"></div>
            </div>
            <div class="upgrade-plans">
                <div class="upgrade-plan">
                    <div class="upgrade-plan__name">Explorer</div>
                    <div class="upgrade-plan__perk">5 mensajes / día</div>
                    <div class="upgrade-plan__perk">Fotos privadas</div>
                    <div class="upgrade-plan__perk">Sala general</div>
                    <div class="upgrade-plan__perk">Sin publicidad</div>
                </div>
                <div class="upgrade-plan is-featured">
                    <div class="upgrade-plan__name">✨ Influencer</div>
                    <div class="upgrade-plan__perk">Mensajes ilimitados</div>
                    <div class="upgrade-plan__perk">Prioridad en búsquedas</div>
                    <div class="upgrade-plan__perk">Videollamadas privadas</div>
                    <div class="upgrade-plan__perk">Perfil destacado</div>
                </div>
            </div>
            <a href="/membresias" class="upgrade-cta">
                🚀 Ver planes y precios
            </a>
            <button class="upgrade-dismiss" id="upgradeDismiss">
                Solo quiero seguir leyendo
            </button>
        </div>
    </div>
    </main>

    {{-- PANEL DERECHO --}}
    <aside class="ch-panel" id="chPanel">

        <div class="ch-panel__header">
            <span>Usuarios</span>
            <span class="count" id="chPanelCount">0</span>
        </div>

        <div id="chOnlineSection">
            <p class="ch-panel__section-title">En linea</p>
            <div id="chOnlineList">
                <p style="padding:.5rem .875rem;font-size:.75rem;color:var(--ch-muted);">
                    Nadie conectado aun
                </p>
            </div>
        </div>

        <div id="chRecentSection">
            <p class="ch-panel__section-title">Recientes</p>
            <div id="chRecentList"></div>
        </div>

        @unless($esMiembro)
        <div class="ch-upgrade-card">
            <div class="ch-upgrade-card__crown">👑</div>
            <div class="ch-upgrade-card__title">Hazte Miembro</div>
            <div class="ch-upgrade-card__sub">
                Desbloquea mensajes ilimitados, sala general y mucho mas.
            </div>
            <div class="ch-upgrade-card__perks">
                <div class="ch-upgrade-card__perk">
                    <i class="fas fa-check"></i> Sala General desbloqueada
                </div>
                <div class="ch-upgrade-card__perk">
                    <i class="fas fa-check"></i> Mensajes privados ilimitados
                </div>
                <div class="ch-upgrade-card__perk">
                    <i class="fas fa-check"></i> Ver fotos privadas
                </div>
                <div class="ch-upgrade-card__perk">
                    <i class="fas fa-check"></i> Perfil verificado
                </div>
            </div>
            <a href="{{ route('membership.index') }}" class="ch-upgrade-card__btn">
                <i class="fas fa-crown"></i> Ver planes
            </a>
        </div>
        @endunless

    </aside>

</div>

<div class="l69-ann-modal" id="annModal" style="display:none;"></div>

    {{-- ═══ VIDEO CALL: Modal llamada activa ════════════════════════════ --}}
    <div id="vcModal" class="vc-modal hidden">
        <div class="vc-videos">
            <video id="vcRemoteVideo" class="vc-remote" autoplay playsinline></video>
            <video id="vcLocalVideo"  class="vc-local"  autoplay playsinline muted></video>
            <span  class="vc-remote-name" id="vcRemoteName"></span>
            <div   class="vc-status-overlay"><span id="vcStatus">Conectando...</span></div>
        </div>
        <div id="vcTimerWrap" class="vc-timer-wrap hidden">
            <div class="vc-bar-bg"><div id="vcTimerBar" class="vc-bar"></div></div>
            <span id="vcTimer" class="vc-timer">--:--</span>
        </div>
        <div class="vc-controls">
            <button id="vcMute"      class="vc-btn vc-btn-mute"
                    onclick="VideoCall.toggleMute()"   title="Silenciar">🎤</button>
            <button id="vcCamToggle" class="vc-btn vc-btn-cam"
                    onclick="VideoCall.toggleCamera()" title="Cámara">📹</button>
            <button id="vcHangup"    class="vc-btn vc-btn-hangup"
                    onclick="VideoCall.endCall('user')" title="Colgar">📵</button>
        </div>
    </div>

    {{-- ═══ VIDEO CALL: Modal llamada entrante ════════════════════════════ --}}
    <div id="vcIncoming" class="vc-incoming hidden">
        <div class="vc-incoming-card">
            <span class="vc-incoming-ring">📹</span>
            <p class="vc-incoming-title">Videollamada entrante</p>
            <p class="vc-incoming-name" id="vcCallerName">...</p>
            <div class="vc-incoming-actions">
                <button class="vc-btn-accept" onclick="VideoCall.acceptCall()">✅ Aceptar</button>
                <button class="vc-btn-reject" onclick="VideoCall.rejectCall()">❌ Rechazar</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ══ Constantes DOM ══ */
    const ME   = @json((string) auth()->id());
    window.ME = ME;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    window.USER_MEMBERSHIP = '{{ auth()->user()->membership_type ?? "free" }}';
    window.USER_ID_STR     = '{{ auth()->user()->id }}';

    /* ── Vistas ── */
    const salaView  = document.getElementById('salaView');
    const dmView    = document.getElementById('dmView');

    /* ── Sala General ── */
    const btnSala   = document.getElementById('btnSalaGeneral');
    const salaCerrar= document.getElementById('salaCerrar');
    const salaMsgs  = document.getElementById('salaMsgs');
    const salaCount = document.getElementById('salaCount');
    const salaInput = document.getElementById('salaInput');
    const salaEnviar= document.getElementById('salaEnviar');
    const salaGateCount = document.getElementById('salaGateCount');

    /* ── DM ── */
    const dmHeaderName    = document.getElementById('dmHeaderName');
    const dmHeaderInitials= document.getElementById('dmHeaderInitials');
    const dmHeaderAvatar  = document.getElementById('dmHeaderAvatar');
    const dmHeaderStatus  = document.getElementById('dmHeaderStatus');
    const dmHeaderLink    = document.getElementById('dmHeaderProfileLink');
    const dmMessages      = document.getElementById('dmMessages');
    const dmLoader        = document.getElementById('dmLoader');
    const typingIndicator = document.getElementById('typingIndicator');
    const chatMsgInput    = document.getElementById('chatMsgInput');
    const chatSendBtn     = document.getElementById('chatSendBtn');
    const chatReceiverId  = document.getElementById('chatReceiverId');

    /* ── Panel online ── */
    const chOnlineList  = document.getElementById('chOnlineList');
    const chRecentList  = document.getElementById('chRecentList');
    const chPanelCount  = document.getElementById('chPanelCount');

    /* ══ Delegacion de clicks en panel online ══ */
    document.addEventListener('click', function(e) {
        const item = e.target.closest('.ch-online-item');
        if (!item) return;
        openChatFromPanel(item.dataset.uid, item.dataset.uname, item.dataset.uavatar || '');
    });

    /* ── Sidebar DMs ── */
    const chSearchInput = document.getElementById('chSearchInput');

    /* ══ Estado ══ */
    let currentDmId   = null;
    let dmChannel     = null;
    let salaChannel   = null;
    let salaOpen      = false;
    let typingTimer   = null;
    let isTyping      = false;

    /* ══ Helpers ══ */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    window.escHtml = escHtml;

    function fmtTime(ts) {
        if (!ts) return '';
        const d = new Date(ts);
        return d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    function fmtDate(ts) {
        if (!ts) return '';
        const d   = new Date(ts);
        const hoy = new Date();
        if (d.toDateString() === hoy.toDateString()) return 'Hoy';
        const ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
        if (d.toDateString() === ayer.toDateString()) return 'Ayer';
        return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' });
    }

    /* ══ Mostrar / ocultar vistas ══ */
    function showSala() {
        if (dmView)   { dmView.style.display   = 'none'; }
        if (salaView) { salaView.style.display  = 'flex'; }
        salaOpen = true;
        if (btnSala) btnSala.classList.add('is-active');
        /* Marcar canal activo en sidebar */
        document.querySelectorAll('.ch-dm').forEach(el => el.classList.remove('is-active'));
        if (btnSala) btnSala.closest('.ch-channel')?.classList.add('is-active');
        bootSala();
        if (salaInput) setTimeout(() => salaInput.focus(), 100);
    }

    function hideSala() {
        if (salaView) salaView.style.display = 'none';
        salaOpen = false;
        if (btnSala) btnSala.classList.remove('is-active');
    }

    function showDm(partnerId, name, avatarPhotoId) {
        hideSala();
        if (salaView) salaView.style.display = 'none';
        if (dmView)   dmView.style.display   = 'flex';

        /* Marcar activo en sidebar */
        document.querySelectorAll('.ch-dm').forEach(el => el.classList.remove('is-active'));
        const dmRow = document.getElementById('dm-' + partnerId);
        if (dmRow) dmRow.classList.add('is-active');

        /* Header */
        if (dmHeaderName)     dmHeaderName.textContent     = name;
        // Exponer datos del contacto para el botón de videollamada
        window._dmCurrentUserId   = partnerId;
        window._dmCurrentUserName = name;
        // Mostrar/ocultar botón según membresía del usuario actual
        const vcBtn = document.getElementById('dmVideoCallBtn');
        if (vcBtn) {
            vcBtn.style.display = (window.USER_MEMBERSHIP && window.USER_MEMBERSHIP !== 'free')
                ? 'inline-flex' : 'none';
        }
        if (dmHeaderInitials) dmHeaderInitials.textContent = name.charAt(0).toUpperCase();
        if (dmHeaderStatus)   dmHeaderStatus.textContent   = '';
        if (dmHeaderLink)     dmHeaderLink.href = '/perfil/' + partnerId;

        /* Avatar */
        if (dmHeaderAvatar) {
            const img = dmHeaderAvatar.querySelector('img');
            if (avatarPhotoId) {
                if (!img) {
                    const i = document.createElement('img');
                    i.src = '/fotos/' + avatarPhotoId + '/ver';
                    i.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:50%';
                    dmHeaderAvatar.innerHTML = '';
                    dmHeaderAvatar.appendChild(i);
                } else {
                    img.src = '/fotos/' + avatarPhotoId + '/ver';
                }
            } else {
                dmHeaderAvatar.innerHTML = '<span id="dmHeaderInitials">' +
                    escHtml(name.charAt(0).toUpperCase()) + '</span>';
            }
        }

        currentDmId = String(partnerId);
        chatReceiverId.value = currentDmId;
        if (chatSendBtn) chatSendBtn.disabled = false;

        loadDmMessages(currentDmId);
        subscribeDmChannel(currentDmId);
    }

    /* ══ Construir burbuja DM ══ */
    function buildBubble(msg, isOwn) {
        const wrap = document.createElement('div');
        wrap.className = 'ch-bubble-wrap' + (isOwn ? ' is-own' : '');

        const bubble = document.createElement('div');
        bubble.className = 'ch-bubble';
        bubble.textContent = msg.body || msg.message || '';

        const time = document.createElement('div');
        time.className = 'ch-bubble__time';
        time.textContent = fmtTime(msg.created_at);

        wrap.appendChild(bubble);
        wrap.appendChild(time);
        return wrap;
    }

    function insertDateSep(container, dateStr) {
        const sep = document.createElement('div');
        sep.className = 'ch-date-sep';
        sep.textContent = dateStr;
        container.appendChild(sep);
    }

    /* ══ Cargar mensajes DM ══ */
    function loadDmMessages(partnerId) {
        if (!dmMessages || !dmLoader) return;
        dmLoader.style.display = 'flex';
        /* Limpiar mensajes anteriores excepto loader y typing */
        Array.from(dmMessages.children).forEach(el => {
            if (el !== dmLoader && el !== typingIndicator) el.remove();
        });

        fetch('/mensajes/conversacion/' + partnerId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest',
                       'Accept': 'application/json',
                       'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            dmLoader.style.display = 'none';
            const messages = data.messages || data || [];
            let lastDate = null;
            messages.forEach(msg => {
                const dateStr = fmtDate(msg.created_at);
                if (dateStr !== lastDate) {
                    insertDateSep(dmMessages, dateStr);
                    lastDate = dateStr;
                }
                const isOwn = String(msg.sender_id) === ME;
                const el = buildBubble(msg, isOwn);
                dmMessages.insertBefore(el, typingIndicator);
            });
            dmMessages.scrollTop = dmMessages.scrollHeight;

            /* Gate / contador */
            if (data.gate) renderDmGate(data.gate);
            if (data.remaining !== undefined) renderDmCounter(data.remaining);
        })
        .catch(() => { dmLoader.style.display = 'none'; });
    }

    function renderDmGate(gate) {
        const dmGate = document.getElementById('dmGate');
        if (!dmGate) return;
        if (gate.type === 'locked') {
            dmGate.style.display = 'block';
            dmGate.innerHTML =
                '<div class="ch-gate" style="margin:0.5rem 1rem;border-radius:10px;">' +
                '<div class="ch-gate__txt"><strong>' + escHtml(gate.title || 'Contenido bloqueado') + '</strong>' +
                '<span>' + escHtml(gate.message || '') + '</span></div>' +
                '<a href="/membresia" class="ch-gate__cta" style="margin-top:0.5rem">' +
                '<i class="fas fa-crown"></i> Ver planes</a></div>';
        } else {
            dmGate.style.display = 'none';
        }
    }

    function renderDmCounter(remaining) {
        const dmCounter    = document.getElementById('dmCounter');
        const dmCounterNum = document.getElementById('dmCounterNum');
        if (!dmCounter) return;
        if (remaining !== null && remaining <= 10) {
            dmCounter.style.display = 'flex';
            if (dmCounterNum) dmCounterNum.textContent = remaining;
            if (remaining <= 0 && chatMsgInput) {
                chatMsgInput.disabled = true;
                chatMsgInput.placeholder = 'Limite alcanzado — hazte miembro';
                if (chatSendBtn) chatSendBtn.disabled = true;
            }
        } else {
            dmCounter.style.display = 'none';
        }
    }

    /* ══ Enviar mensaje DM ══ */
    function sendDmMsg() {
        if (!chatMsgInput || !currentDmId) return;
        const text = chatMsgInput.value.trim();
        if (!text) return;

        chatSendBtn.disabled = true;

        fetch('/mensajes/enviar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ receiver_id: currentDmId, body: text })
        })
        .then(function(r) {
            return r.json().then(function(d) {
                return { status: r.status, data: d };
            });
        })
        .then(function(res) {
            chatSendBtn.disabled = false;
            if (res.status === 429) {
                showUpgradeModal(res.data.sent_today, res.data.limit);
                return;
            }
            if (res.status === 403) {
                showToast('Sin acceso', res.data.message || 'Necesitas membresia para enviar mensajes');
                return;
            }
            chatMsgInput.value = '';
            chatMsgInput.style.height = 'auto';
            const fakeMsg = { body: text, created_at: new Date().toISOString() };
            const el = buildBubble(fakeMsg, true);
            dmMessages.insertBefore(el, typingIndicator);
            dmMessages.scrollTop = dmMessages.scrollHeight;
            updateDmPreview(currentDmId, text);
            if (res.data.remaining !== undefined) renderDmCounter(res.data.remaining);
        })
        .catch(function() { chatSendBtn.disabled = false; });
    }

    function updateDmPreview(partnerId, text) {
        const row = document.getElementById('dm-' + partnerId);
        if (!row) return;
        const preview = row.querySelector('.ch-dm__preview');
        if (preview) preview.innerHTML = '<span style="opacity:.7">Tu: </span>' + escHtml(text.substring(0, 36));
        const time = row.querySelector('.ch-dm__time');
        if (time) time.textContent = 'ahora';
        const count = row.querySelector('.ch-dm__count');
        if (count) count.remove();
    }

    /* ══ Canal Reverb DM ══ */
    function subscribeDmChannel(partnerId) {
        if (dmChannel) {
            try { window.Echo.leave('chat.' + (dmChannel._partnerId || '')); } catch(e) {}
        }
        if (!window.Echo) return;
        dmChannel = window.Echo.private('chat.' + ME);
        dmChannel._partnerId = partnerId;

        dmChannel.listen('.MessageSent', function(e) {
            if (String(e.sender_id) !== String(partnerId)) return;
            const el = buildBubble(e, false);
            dmMessages.insertBefore(el, typingIndicator);
            dmMessages.scrollTop = dmMessages.scrollHeight;
            updateDmPreview(partnerId, e.body || e.message || '');
        });

        dmChannel.listenForWhisper('typing', function(e) {
            if (String(e.user_id) !== String(partnerId)) return;
            if (typingIndicator) typingIndicator.classList.add('is-visible');
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                if (typingIndicator) typingIndicator.classList.remove('is-visible');
            }, 3000);
        });
    }

    /* ══ Sala General ══ */
    function bootSala() {
        if (salaChannel || !window.Echo) return;

        salaChannel = window.Echo.join('presence-sala-general')
            .here(function(users) {
                const n = users.length;
                if (salaCount) salaCount.textContent = n;
                if (salaGateCount) salaGateCount.textContent = n + ' miembro' + (n !== 1 ? 's' : '');
                if (chPanelCount)  chPanelCount.textContent  = n;
                console.log('[Sala] en sala:', n);
            })
            .joining(function(user) {
                const n = parseInt(salaCount?.textContent || 0) + 1;
                if (salaCount) salaCount.textContent = n;
                appendSalaMsg('Sistema', user.name + ' entro a la sala 👋', false, true);
            })
            .leaving(function(user) {
                const n = Math.max(0, parseInt(salaCount?.textContent || 0) - 1);
                if (salaCount) salaCount.textContent = n;
                appendSalaMsg('Sistema', user.name + ' salio de la sala', false, true);
            })
            .listenForWhisper('sala-msg', function(e) {
                appendSalaMsg(e.name || 'Usuario', e.text, false, false);
            });
    }

    function appendSalaMsg(name, text, isOwn, isSys) {
        if (!salaMsgs) return;
        if (isSys) {
            const d = document.createElement('div');
            d.className = 'ch-sys-msg';
            d.textContent = text;
            salaMsgs.appendChild(d);
        } else {
            const wrap = document.createElement('div');
            wrap.className = 'ch-bubble-wrap' + (isOwn ? ' is-own' : '');
            wrap.innerHTML =
                '<div class="ch-bubble__author">' + escHtml(name) + '</div>' +
                '<div class="ch-bubble">' + escHtml(text) + '</div>';
            salaMsgs.appendChild(wrap);
        }
        salaMsgs.scrollTop = salaMsgs.scrollHeight;
    }

    function sendSalaMsg() {
        if (!salaChannel || !salaInput) return;
        const text = salaInput.value.trim();
        if (!text) return;
        const myName = window.__usersCache?.[ME]?.name || 'Yo';
        salaChannel.whisper('sala-msg', { text: text, name: myName });
        appendSalaMsg(myName, text, true, false);
        salaInput.value = '';
    }

    /* ══ Panel online (Reverb presence) ══ */
    window.__usersCache = {};

    function renderOnlinePanel(users, total) {
        window.__usersCache = {};
        users.forEach(u => { window.__usersCache[String(u.id)] = u; });

        if (chPanelCount) chPanelCount.textContent = total;

        const online  = users.filter(u => u.online !== false);
        const offline = users.filter(u => u.online === false);

        /* Online */
        if (chOnlineList) {
            if (online.length === 0) {
                chOnlineList.innerHTML =
                    '<p style="padding:.5rem .875rem;font-size:.75rem;color:var(--ch-muted);">Nadie conectado aun</p>';
            } else {
                chOnlineList.innerHTML = online.map(u => buildOnlineItem(u, true)).join('');
            }
        }

        /* Recientes */
        if (chRecentList) {
            chRecentList.innerHTML = offline.map(u => buildOnlineItem(u, false)).join('');
        }

        /* Dots en DM list */
        users.forEach(u => {
            const dot = document.getElementById('dot-' + u.id);
            if (dot) dot.classList.toggle('is-online', !!u.online);
        });
    }

    function buildOnlineItem(u, isOnline) {
        const initials = (u.name || '?').charAt(0).toUpperCase();
        const avatarHtml = u.avatar
            ? '<img src="' + escHtml(u.avatar) + '" style="width:100%;height:100%;object-fit:cover;">'
            : initials;
        const div = document.createElement('div');
        div.className = 'ch-online-item';
        div.dataset.uid    = String(u.id);
        div.dataset.uname  = u.name || '';
        div.dataset.uavatar = u.avatar_photo_id || '';
        div.innerHTML =
            '<div class="ch-online-item__avatar">' + avatarHtml +
            '<span class="ch-online-item__status' + (isOnline ? ' is-online' : '') + '"></span>' +
            '</div>' +
            '<span class="ch-online-item__name">' + escHtml(u.name || 'Usuario') + '</span>';
        div.addEventListener('click', function() {
            openChatFromPanel(div.dataset.uid, div.dataset.uname, div.dataset.uavatar);
        });
        return div.outerHTML;
    }

    /* ══ Reverb — canal presence global ══ */
    function bootReverb() {
        if (!window.Echo) {
            console.warn('[Chat] Echo no disponible');
            return;
        }

        const presenceChannel = window.Echo.join('presence-lobby')
            .here(function(users) {
                console.log('[Lobby] Online:', users.length);
                renderOnlinePanel(users.map(u => ({...u, online: true})), users.length);
            })
            .joining(function(user) {
                console.log('[Lobby] Joined:', user.name);
                window.__usersCache[String(user.id)] = {...user, online: true};
                renderOnlinePanel(Object.values(window.__usersCache), Object.keys(window.__usersCache).length);
            })
            .leaving(function(user) {
                console.log('[Lobby] Left:', user.name);
                if (window.__usersCache[String(user.id)]) {
                    window.__usersCache[String(user.id)].online = false;
                }
                renderOnlinePanel(Object.values(window.__usersCache), Object.keys(window.__usersCache).length);
            });

        /* Escuchar mensajes privados en canal propio */
        window.Echo.private('chat.' + ME)
            .listen('.MessageSent', function(e) {
                if (String(e.sender_id) === String(currentDmId)) {
                    const el = buildBubble(e, false);
                    if (dmMessages && typingIndicator) {
                        dmMessages.insertBefore(el, typingIndicator);
                        dmMessages.scrollTop = dmMessages.scrollHeight;
                    }
                } else {
                    showToast(e.sender_name || 'Nuevo mensaje',
                              e.body || e.message || '');
                    const dot = document.getElementById('dot-' + e.sender_id);
                    if (dot) dot.classList.add('is-online');
                }
            });

        console.log('[Chat] Reverb listo');
    }

    /* ══ Toast notificacion ══ */

    /* ══ Modal Upgrade ══ */
    const upgradeOverlay     = document.getElementById('upgradeOverlay');
    const upgradeUsed        = document.getElementById('upgradeUsed');
    const upgradeLimit       = document.getElementById('upgradeLimit');
    const upgradeProgressBar = document.getElementById('upgradeProgressBar');
    const upgradeDismiss     = document.getElementById('upgradeDismiss');

    if (upgradeDismiss) {
        upgradeDismiss.addEventListener('click', function() {
            if (upgradeOverlay) upgradeOverlay.classList.remove('is-visible');
        });
    }
    if (upgradeOverlay) {
        upgradeOverlay.addEventListener('click', function(e) {
            if (e.target === upgradeOverlay) upgradeOverlay.classList.remove('is-visible');
        });
    }

    function showUpgradeModal(used, limit) {
        if (!upgradeOverlay) return;
        if (upgradeUsed)  upgradeUsed.textContent  = used  ?? limit ?? 5;
        if (upgradeLimit) upgradeLimit.textContent = limit ?? 5;
        const pct = limit ? Math.min(100, Math.round((used / limit) * 100)) : 100;
        if (upgradeProgressBar) upgradeProgressBar.style.width = pct + '%';
        upgradeOverlay.classList.add('is-visible');
    }
    window.showUpgradeModal = showUpgradeModal;

    function showToast(nick, body) {
        const container = document.getElementById('chToastContainer') ||
            (() => {
                const d = document.createElement('div');
                d.id = 'chToastContainer';
                d.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;';
                document.body.appendChild(d);
                return d;
            })();

        const t = document.createElement('div');
        t.style.cssText = 'background:var(--theme-card);border:1px solid var(--ch-border);' +
            'border-radius:10px;padding:.65rem 1rem;font-size:.82rem;color:var(--ch-text);' +
            'box-shadow:0 4px 16px rgba(0,0,0,.2);max-width:280px;cursor:pointer;' +
            'animation:ch-bubble-in .2s ease;';
        t.innerHTML = '<div style="font-weight:700;margin-bottom:2px;">💬 ' + escHtml(nick) + '</div>' +
                      '<div style="color:var(--ch-muted);">' + escHtml(String(body).substring(0, 70)) + '</div>';
        t.onclick = function() { t.remove(); };
        container.appendChild(t);
        setTimeout(() => { if (t.parentNode) t.remove(); }, 4500);
    }

    /* ══ Búsqueda en sidebar ══ */
    if (chSearchInput) {
        chSearchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.ch-dm').forEach(row => {
                const name = (row.dataset.name || '').toLowerCase();
                row.style.display = (!q || name.includes(q)) ? '' : 'none';
            });
        });
    }

    /* ══ Eventos DM rows en sidebar ══ */
    document.querySelectorAll('.ch-dm').forEach(row => {
        row.addEventListener('click', function() {
            const pid    = this.dataset.partner;
            const name   = this.dataset.name   || 'Usuario';
            const avatar = this.dataset.avatar  || '';
            showDm(pid, name, avatar);
        });
    });

    /* ══ Eventos Sala General ══ */
    if (btnSala) {
        btnSala.addEventListener('click', function(e) {
            e.preventDefault();
            if (salaOpen) { hideSala(); } else { showSala(); }
        });
    }

    if (salaCerrar) {
        salaCerrar.addEventListener('click', function() { hideSala(); });
    }

    if (salaEnviar) {
        salaEnviar.addEventListener('click', sendSalaMsg);
    }

    if (salaInput) {
        salaInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendSalaMsg();
            }
        });
    }

    /* ══ Eventos DM input ══ */
    if (chatMsgInput) {
        chatMsgInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            if (chatSendBtn) chatSendBtn.disabled = !this.value.trim();

            /* Whisper typing */
            if (dmChannel && currentDmId && !isTyping) {
                isTyping = true;
                dmChannel.whisper('typing', { user_id: ME });
                setTimeout(() => { isTyping = false; }, 2500);
            }
        });

        chatMsgInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!chatSendBtn.disabled) sendDmMsg();
            }
        });
    }

    if (chatSendBtn) {
        chatSendBtn.addEventListener('click', function() {
            if (!this.disabled) sendDmMsg();
        });
    }

    /* ══ Boton volver (movil) ══ */
    const btnBackDm = document.getElementById('btnBackDm');
    if (btnBackDm) {
        btnBackDm.addEventListener('click', function() {
            if (dmView) dmView.style.display = 'none';
            currentDmId = null;
        });
    }

    const btnBackSala = document.getElementById('btnBackSala');
    if (btnBackSala) {
        btnBackSala.addEventListener('click', function() { hideSala(); });
    }

    /* ══ API publica ══ */
    window.openChat = function(partnerId, name, avatarPhotoId) {
        showDm(partnerId, name, avatarPhotoId);
    };
    window.openChatFromPanel = function(partnerId, name, avatarPhotoId) {
        showDm(partnerId, name, avatarPhotoId);
    };
    window.openSala  = showSala;
    window.closeSala = hideSala;

    /* ══ Arrancar ══ */
    setTimeout(bootReverb, 400);

    /* Abrir sala general automaticamente al cargar */
    setTimeout(function() { showSala(); }, 600);

})();
</script>
    {{-- ═══ VIDEO CALL SCRIPTS ════════════════════════════════════════════ --}}
    <script src="https://unpkg.com/simple-peer@9.11.1/simplepeer.min.js"></script>
    <script src="{{ asset('js/video-call.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar módulo de videollamada con el ID del usuario actual
        if (window.Echo && window.ME) {
            VideoCall.init(ME);
            console.log('[VideoCall] Inicializado para user:', ME);
        }
    });

    /**
     * Abre videollamada desde el header del chat privado.
     * Se llama desde el botón que inyectamos en openChat().
     */
    window.startVideoCall = function(toUserId, remoteName) {
        VideoCall.call(toUserId, remoteName);
    };
    </script>

@endpush

































