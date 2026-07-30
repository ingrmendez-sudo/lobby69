/**
 * VideoCall — Módulo de videollamada 1:1 para LOBBY69
 * Señalización: Laravel Echo (Reverb) | WebRTC: SimplePeer
 *
 * Reglas de negocio:
 *   lifetime     => 50 min máximo por sesión
 *   premium_plus => 30 min máximo por sesión
 *   premium      => 30 min máximo por sesión
 *   basic        => 30 min máximo por sesión
 *   Cooldown     => 2 min entre sesiones
 */
window.VideoCall = (function () {

    // ── Servidores ICE (STUN público + TURN propio) ─────────────────────────
    const ICE_SERVERS = [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        // Cuando instales Coturn, descomenta y configura:
        // {
        //     urls:       'turn:turn.lobby69.com:3478',
        //     username:   window.TURN_USER || 'lobby69',
        //     credential: window.TURN_PASS || 'changeme',
        // },
    ];

    // ── Estado interno ──────────────────────────────────────────────────────
    let S = {
        peer:               null,
        localStream:        null,
        token:              null,
        maxSecs:            null,
        elapsed:            0,
        timer:              null,
        warningShown:       false,
        remoteUserId:       null,
        isInitiator:        false,
        pendingToken:       null,
        pendingFrom:        null,
        pendingMaxSecs:     null,
        incomingTimeout:    null,
    };

    // ── Referencias DOM ─────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);

    const DOM = {
        // Modal llamada activa
        modal:        () => $('vcModal'),
        localVideo:   () => $('vcLocalVideo'),
        remoteVideo:  () => $('vcRemoteVideo'),
        remoteName:   () => $('vcRemoteName'),
        timerEl:      () => $('vcTimer'),
        timerBar:     () => $('vcTimerBar'),
        timerWrap:    () => $('vcTimerWrap'),
        statusEl:     () => $('vcStatus'),
        muteBtn:      () => $('vcMute'),
        camBtn:       () => $('vcCamToggle'),
        hangupBtn:    () => $('vcHangup'),
        // Modal llamada entrante
        incomingModal:() => $('vcIncoming'),
        callerName:   () => $('vcCallerName'),
        acceptBtn:    () => $('vcAccept'),
        rejectBtn:    () => $('vcReject'),
    };

    // ── Helpers de UI ───────────────────────────────────────────────────────
    function show(el) { el && el.classList.remove('hidden'); }
    function hide(el) { el && el.classList.add('hidden'); }
    function setStatus(msg) {
        const el = DOM.statusEl();
        if (el) el.textContent = msg;
    }

    // ── Inicializar — suscribir canal propio en Reverb ──────────────────────
    function init(myUserId) {
        if (!window.Echo) {
            console.error('[VideoCall] window.Echo no está disponible.');
            return;
        }
        window.Echo
            .private('video.user.' + myUserId)
            .listen('.VideoSignal', onSignal);

        console.log('[VideoCall] Escuchando canal video.user.' + myUserId);
    }

    // ── Manejador central de señales ────────────────────────────────────────
    function onSignal(data) {
        console.log('[VideoCall] Señal recibida:', data.type, data);

        switch (data.type) {
            case 'call-request':
                showIncoming(data);
                break;
            case 'call-accepted':
                S.token = data.token;
                setStatus('Conectando...');
                createPeer(true);
                break;
            case 'call-rejected':
                toast('📵 Llamada rechazada', '', 'warning');
                cleanup();
                break;
            case 'offer':
                if (!S.peer) {
                    S.remoteUserId = data.from_user_id;
                    createPeer(false);
                }
                S.peer.signal(data.payload);
                break;
            case 'answer':
                S.peer && S.peer.signal(data.payload);
                break;
            case 'ice-candidate':
                S.peer && S.peer.signal(data.payload);
                break;
            case 'call-ended':
                const reason = data.payload && data.payload.reason === 'timeout'
                    ? 'timeout' : 'remote';
                cleanup(reason);
                break;
        }
    }

    // ── Iniciar llamada saliente ────────────────────────────────────────────
    async function call(toUserId, remoteName) {
        if (S.peer) {
            toast('⚠️ Ya tienes una llamada activa', '', 'warning');
            return;
        }

        S.remoteUserId  = toUserId;
        S.isInitiator   = true;

        try {
            S.localStream = await getMedia();
        } catch (e) {
            toast('🎥 Sin acceso a cámara/micrófono', e.message, 'error');
            return;
        }

        DOM.localVideo().srcObject  = S.localStream;
        DOM.remoteName() && (DOM.remoteName().textContent = remoteName || 'Usuario');
        show(DOM.modal());
        setStatus('Llamando a ' + (remoteName || 'usuario') + '...');

        const res = await apiFetch('/video/initiate', { to_user_id: toUserId });

        if (!res.ok) {
            const err = await res.json();
            handleApiError(err);
            cleanup();
            return;
        }

        const data       = await res.json();
        S.token          = data.token;
        S.maxSecs        = data.max_duration_seconds;
    }

    // ── Mostrar llamada entrante ────────────────────────────────────────────
    function showIncoming(data) {
        S.pendingToken   = data.token;
        S.pendingFrom    = data.from_user_id;
        S.pendingMaxSecs = data.max_duration_seconds;

        const nameEl = DOM.callerName();
        if (nameEl) nameEl.textContent = data.payload.caller_name || 'Alguien';

        show(DOM.incomingModal());

        // Auto-rechaza si no responde en 30 segundos
        S.incomingTimeout = setTimeout(() => rejectCall(), 30000);
    }

    // ── Aceptar llamada ─────────────────────────────────────────────────────
    async function acceptCall() {
        clearTimeout(S.incomingTimeout);
        hide(DOM.incomingModal());

        S.token          = S.pendingToken;
        S.remoteUserId   = S.pendingFrom;
        S.maxSecs        = S.pendingMaxSecs;
        S.isInitiator    = false;

        try {
            S.localStream = await getMedia();
        } catch (e) {
            toast('🎥 Sin acceso a cámara/micrófono', e.message, 'error');
            await apiFetch('/video/respond', { token: S.token, action: 'reject' });
            return;
        }

        DOM.localVideo().srcObject = S.localStream;
        show(DOM.modal());
        setStatus('Conectando...');

        await apiFetch('/video/respond', { token: S.token, action: 'accept' });
    }

    // ── Rechazar llamada ────────────────────────────────────────────────────
    async function rejectCall() {
        clearTimeout(S.incomingTimeout);
        hide(DOM.incomingModal());

        if (S.pendingToken) {
            await apiFetch('/video/respond', {
                token:  S.pendingToken,
                action: 'reject',
            });
        }
        S.pendingToken = null;
    }

    // ── Crear peer WebRTC ───────────────────────────────────────────────────
    function createPeer(initiator) {
        if (!window.SimplePeer) {
            console.error('[VideoCall] SimplePeer no está cargado.');
            cleanup('error');
            return;
        }

        S.peer = new SimplePeer({
            initiator,
            stream:  S.localStream,
            config:  { iceServers: ICE_SERVERS },
            trickle: true,
        });

        // Enviar señal al otro peer via backend
        S.peer.on('signal', async (signalData) => {
            const type = signalData.type || 'ice-candidate';
            await apiFetch('/video/signal', {
                token:      S.token,
                to_user_id: S.remoteUserId,
                type,
                payload:    signalData,
            });
        });

        // Stream remoto recibido — llamada conectada
        S.peer.on('stream', (remoteStream) => {
            DOM.remoteVideo().srcObject = remoteStream;
            setStatus('');
            startTimer();
            show(DOM.timerWrap());
        });

        S.peer.on('error', (err) => {
            console.error('[VideoCall] Peer error:', err);
            toast('❌ Error de conexión', err.message, 'error');
            cleanup('error');
        });

        S.peer.on('close', () => cleanup('remote'));
    }

    // ── Timer de cuenta regresiva ───────────────────────────────────────────
    function startTimer() {
        S.elapsed      = 0;
        S.warningShown = false;

        S.timer = setInterval(() => {
            S.elapsed++;
            const remaining = S.maxSecs - S.elapsed;

            updateTimerUI(remaining);

            // Advertencia a 2 minutos del límite
            if (remaining === 120 && !S.warningShown) {
                S.warningShown = true;
                injectToastInModal(
                    '⏱️ La llamada termina en 2 minutos',
                    'Puedes iniciar una nueva después de 2 min de pausa.'
                );
            }

            // Cierre automático al llegar a 0
            if (remaining <= 0) {
                endCall('timeout');
            }
        }, 1000);
    }

    function updateTimerUI(remaining) {
        const isWarning = remaining <= 120;
        const abs       = Math.abs(remaining);
        const mm        = Math.floor(abs / 60).toString().padStart(2, '0');
        const ss        = (abs % 60).toString().padStart(2, '0');
        const label     = remaining < 0 ? '-' : '';

        const timerEl = DOM.timerEl();
        const barEl   = DOM.timerBar();

        if (timerEl) {
            timerEl.textContent = label + mm + ':' + ss;
            timerEl.className   = 'vc-timer' + (isWarning ? ' warning' : '');
        }

        if (barEl && S.maxSecs) {
            const pct = Math.max(0, (remaining / S.maxSecs) * 100);
            barEl.style.width = pct + '%';
            barEl.className   = 'vc-bar' + (isWarning ? ' warning' : '');
        }
    }

    function injectToastInModal(title, subtitle) {
        const modal = DOM.modal();
        if (!modal) return;
        const div = document.createElement('div');
        div.className   = 'vc-inline-toast';
        div.innerHTML   = '<strong>' + title + '</strong><br><small>' + subtitle + '</small>';
        modal.appendChild(div);
        setTimeout(() => div.remove(), 10000);
    }

    // ── Colgar ──────────────────────────────────────────────────────────────
    async function endCall(reason) {
        reason = reason || 'user';
        clearInterval(S.timer);

        if (S.token) {
            try {
                await apiFetch('/video/end', { token: S.token });
            } catch (e) {
                console.warn('[VideoCall] Error al cerrar sesión:', e);
            }
        }

        cleanup(reason);
    }

    // ── Controles de micrófono y cámara ─────────────────────────────────────
    function toggleMute() {
        if (!S.localStream) return;
        const audioTrack = S.localStream.getAudioTracks()[0];
        if (!audioTrack) return;
        audioTrack.enabled = !audioTrack.enabled;
        const btn = DOM.muteBtn();
        if (btn) btn.textContent = audioTrack.enabled ? '🎤' : '🔇';
    }

    function toggleCamera() {
        if (!S.localStream) return;
        const videoTrack = S.localStream.getVideoTracks()[0];
        if (!videoTrack) return;
        videoTrack.enabled = !videoTrack.enabled;
        const btn = DOM.camBtn();
        if (btn) btn.textContent = videoTrack.enabled ? '📹' : '📷';
    }

    // ── Limpieza de estado ──────────────────────────────────────────────────
    function cleanup(reason) {
        clearInterval(S.timer);
        clearTimeout(S.incomingTimeout);

        if (S.peer) { try { S.peer.destroy(); } catch(e) {} }
        if (S.localStream) S.localStream.getTracks().forEach(t => t.stop());

        hide(DOM.modal());
        hide(DOM.incomingModal());

        const localVideo  = DOM.localVideo();
        const remoteVideo = DOM.remoteVideo();
        if (localVideo)  localVideo.srcObject  = null;
        if (remoteVideo) remoteVideo.srcObject = null;

        if (reason === 'timeout') {
            toast(
                '⏱️ Tiempo de llamada agotado',
                'Puedes iniciar una nueva en 2 minutos.',
                'info'
            );
        } else if (reason === 'remote') {
            toast('📵 La llamada fue terminada', '', 'info');
        } else if (reason === 'error') {
            toast('❌ Error en la videollamada', 'Inténtalo de nuevo.', 'error');
        }

        // Reset estado (preserva el canal Echo)
        S = {
            ...S,
            peer:           null,
            localStream:    null,
            token:          null,
            maxSecs:        null,
            elapsed:        0,
            timer:          null,
            warningShown:   false,
            isInitiator:    false,
            pendingToken:   null,
            pendingFrom:    null,
            pendingMaxSecs: null,
        };
    }

    // ── Helpers internos ────────────────────────────────────────────────────
    async function getMedia() {
        return navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, frameRate: 24 },
            audio: true,
        });
    }

    async function apiFetch(url, body) {
        return fetch(url, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF || document.querySelector('meta[name=csrf-token]')?.content || '',
            },
            body: JSON.stringify(body),
        });
    }

    function handleApiError(err) {
        const msgs = {
            no_membership:  'Necesitas membresía para videollamar.',
            cooldown:       'Espera ' + (err.wait_seconds || 120) + 's antes de otra llamada.',
            already_in_call:'Ya tienes una llamada activa.',
            invalid_target: 'No puedes llamarte a ti mismo.',
        };
        toast(
            '❌ No se puede iniciar la llamada',
            msgs[err.error] || err.message || 'Error desconocido.',
            'error'
        );
    }

    function toast(title, subtitle, type) {
        if (window.showToast) {
            window.showToast(title, subtitle, type || 'info');
            return;
        }
        console.log('[VideoCall Toast]', title, subtitle);
    }

    // ── API pública ─────────────────────────────────────────────────────────
    return { init, call, acceptCall, rejectCall, endCall, toggleMute, toggleCamera };

})();
