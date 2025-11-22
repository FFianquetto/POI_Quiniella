/**
 * Sistema de Videollamadas WebRTC
 * Implementación simplificada y moderna
 */
class VideoCall {
    constructor(chatId, usuarioId) {
        this.chatId = chatId;
        this.usuarioId = usuarioId;
        
        // Estado de la conexión
        this.peerConnection = null;
        this.localStream = null;
        this.remoteStream = null;
        this.isInCall = false;
        this.isInitiator = false;
        this.callId = null;
        
        // Controles
        this.isAudioMuted = false;
        this.isVideoMuted = false;
        
        // Timer
        this.callStartTime = null;
        this.callTimer = null;
        
        // Señalización
        this.signalingInterval = null;
        this.lastMessageId = 0;
        this.incomingOffer = null;
        this.callNotificationSound = null;
        this.isRinging = false;
        this.ringingInterval = null;
        
        // Configuración WebRTC optimizada para producción
        this.configuration = {
            iceServers: [
                // STUN servers de Google (gratuitos y confiables)
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' },
                { urls: 'stun:stun3.l.google.com:19302' },
                { urls: 'stun:stun4.l.google.com:19302' },
                // STUN alternativos
                { urls: 'stun:stun.stunprotocol.org:3478' },
                // TURN servers públicos (gratuitos, pueden tener limitaciones)
                // Nota: Para producción real, considera usar TURN servers propios
                {
                    urls: 'turn:openrelay.metered.ca:80',
                    username: 'openrelayproject',
                    credential: 'openrelayproject'
                },
                {
                    urls: 'turn:openrelay.metered.ca:443',
                    username: 'openrelayproject',
                    credential: 'openrelayproject'
                },
                {
                    urls: 'turn:openrelay.metered.ca:443?transport=tcp',
                    username: 'openrelayproject',
                    credential: 'openrelayproject'
                }
            ],
            iceCandidatePoolSize: 10,
            iceTransportPolicy: 'all' // Usar tanto UDP como TCP
        };
        
        this.initializeElements();
        this.bindEvents();
        this.initializeCallNotification();
        
        // Iniciar polling automáticamente para detectar llamadas entrantes
        // Solo si no estamos en una llamada activa
        if (!this.isInCall) {
            this.iniciarPolling();
        }
    }
    
    /**
     * Inicializar sistema de notificación de llamadas
     */
    initializeCallNotification() {
        // Crear audio context para sonido de llamada
        try {
            this.callNotificationSound = new Audio();
            // Usar un sonido de llamada simple generado con Web Audio API
            this.createCallSound();
        } catch (error) {
            console.warn('No se pudo inicializar el sistema de notificación de sonido:', error);
        }
    }
    
    /**
     * Crear sonido de llamada usando Web Audio API
     */
    createCallSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.audioContext = audioContext;
        } catch (error) {
            console.warn('No se pudo crear AudioContext:', error);
        }
    }
    
    /**
     * Reproducir sonido de llamada entrante
     */
    playCallSound() {
        if (!this.audioContext) {
            return;
        }
        
        this.isRinging = true;
        
        const playTone = () => {
            if (!this.isRinging || !this.audioContext) {
                return;
            }
            
            try {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);
                
                // Frecuencia de timbre de teléfono (típicamente 440Hz y 480Hz alternando)
                oscillator.frequency.value = 440;
                oscillator.type = 'sine';
                
                // Fade in/out para sonido más suave
                gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.3, this.audioContext.currentTime + 0.1);
                gainNode.gain.linearRampToValueAtTime(0, this.audioContext.currentTime + 0.4);
                
                oscillator.start(this.audioContext.currentTime);
                oscillator.stop(this.audioContext.currentTime + 0.4);
                
                // Segunda frecuencia después de un breve delay
                setTimeout(() => {
                    if (!this.isRinging || !this.audioContext) {
                        return;
                    }
                    
                    const oscillator2 = this.audioContext.createOscillator();
                    const gainNode2 = this.audioContext.createGain();
                    
                    oscillator2.connect(gainNode2);
                    gainNode2.connect(this.audioContext.destination);
                    
                    oscillator2.frequency.value = 480;
                    oscillator2.type = 'sine';
                    
                    gainNode2.gain.setValueAtTime(0, this.audioContext.currentTime);
                    gainNode2.gain.linearRampToValueAtTime(0.3, this.audioContext.currentTime + 0.1);
                    gainNode2.gain.linearRampToValueAtTime(0, this.audioContext.currentTime + 0.4);
                    
                    oscillator2.start(this.audioContext.currentTime);
                    oscillator2.stop(this.audioContext.currentTime + 0.4);
                }, 200);
                
            } catch (error) {
                console.warn('Error al reproducir sonido:', error);
            }
        };
        
        // Reproducir inmediatamente
        playTone();
        
        // Repetir cada segundo mientras está sonando
        this.ringingInterval = setInterval(() => {
            if (this.isRinging && this.audioContext) {
                playTone();
            } else {
                this.stopCallSound();
            }
        }, 1000);
    }
    
    /**
     * Detener sonido de llamada
     */
    stopCallSound() {
        this.isRinging = false;
        if (this.ringingInterval) {
            clearInterval(this.ringingInterval);
            this.ringingInterval = null;
        }
    }
    
    initializeElements() {
        this.btnVideollamada = document.getElementById('btn-videollamada');
        this.modalVideollamada = document.getElementById('modalVideollamada');
        this.videoLocal = document.getElementById('video-local');
        this.videoRemoto = document.getElementById('video-remoto');
        this.estadoVideollamada = document.getElementById('estado-videollamada');
        this.btnToggleAudio = document.getElementById('btn-toggle-audio');
        this.btnToggleVideo = document.getElementById('btn-toggle-video');
        this.btnColgar = document.getElementById('btn-colgar');
        this.btnCerrarVideollamada = document.getElementById('btn-cerrar-videollamada');
        this.tiempoVideollamada = document.getElementById('tiempo-videollamada');
        
        // Modal de llamada entrante
        this.modalLlamadaEntrante = document.getElementById('modalLlamadaEntrante');
        this.btnAceptarLlamada = document.getElementById('btn-aceptar-llamada');
        this.btnRechazarLlamada = document.getElementById('btn-rechazar-llamada');
        this.nombreLlamante = document.getElementById('nombre-llamante');
        
        // Verificar que los elementos críticos existan
        if (!this.btnVideollamada) {
            console.warn('Botón de videollamada no encontrado');
        }
        if (!this.modalVideollamada) {
            console.warn('Modal de videollamada no encontrado');
        }
        if (!this.modalLlamadaEntrante) {
            console.warn('Modal de llamada entrante no encontrado');
        }
    }
    
    bindEvents() {
        // Botón iniciar videollamada
        if (this.btnVideollamada) {
            // Remover listeners anteriores si existen
            const newBtn = this.btnVideollamada.cloneNode(true);
            this.btnVideollamada.parentNode.replaceChild(newBtn, this.btnVideollamada);
            this.btnVideollamada = newBtn;
            
            this.btnVideollamada.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Botón videollamada clickeado');
                this.iniciarVideollamada();
            });
        } else {
            console.error('No se pudo vincular el botón de videollamada');
        }
        
        // Botones de control
        if (this.btnToggleAudio) {
            this.btnToggleAudio.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleAudio();
            });
        }
        
        if (this.btnToggleVideo) {
            this.btnToggleVideo.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleVideo();
            });
        }
        
        if (this.btnColgar) {
            this.btnColgar.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.endCall();
            });
        }
        
        if (this.btnCerrarVideollamada) {
            this.btnCerrarVideollamada.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.endCall();
            });
        }
        
        // Botones de llamada entrante
        if (this.btnAceptarLlamada) {
            // Remover listeners anteriores si existen
            const newAceptar = this.btnAceptarLlamada.cloneNode(true);
            this.btnAceptarLlamada.parentNode.replaceChild(newAceptar, this.btnAceptarLlamada);
            this.btnAceptarLlamada = newAceptar;
            
            this.btnAceptarLlamada.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Botón aceptar llamada clickeado');
                this.aceptarLlamada();
            });
        } else {
            console.error('No se pudo vincular el botón de aceptar llamada');
        }
        
        if (this.btnRechazarLlamada) {
            // Remover listeners anteriores si existen
            const newRechazar = this.btnRechazarLlamada.cloneNode(true);
            this.btnRechazarLlamada.parentNode.replaceChild(newRechazar, this.btnRechazarLlamada);
            this.btnRechazarLlamada = newRechazar;
            
            this.btnRechazarLlamada.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Botón rechazar llamada clickeado');
                this.rechazarLlamada();
            });
        } else {
            console.error('No se pudo vincular el botón de rechazar llamada');
        }
        
        // Eventos del modal
        if (this.modalVideollamada) {
            this.modalVideollamada.addEventListener('hidden.bs.modal', () => {
                if (this.isInCall) {
                    this.endCall();
                }
            });
        }
    }
    
    /**
     * Iniciar una videollamada
     */
    async iniciarVideollamada() {
        try {
            // Validar HTTPS en producción (requerido para WebRTC)
            const isLocalhost = window.location.hostname === 'localhost' || 
                               window.location.hostname === '127.0.0.1' ||
                               window.location.hostname === '0.0.0.0';
            
            if (window.location.protocol !== 'https:' && !isLocalhost) {
                alert('Las videollamadas requieren HTTPS en producción. Por favor, accede al sitio usando HTTPS.');
                return;
            }
            
            // Validar soporte WebRTC
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Tu navegador no soporta videollamadas. Por favor, usa Chrome, Firefox, Edge o Safari (versiones recientes).');
                return;
            }
            
            // Validar RTCPeerConnection
            if (!window.RTCPeerConnection) {
                alert('WebRTC no está disponible en tu navegador. Por favor, actualiza tu navegador.');
                return;
            }
            
            // Validar que tenemos chatId y usuarioId
            if (!this.chatId || !this.usuarioId) {
                console.error('Faltan chatId o usuarioId:', { chatId: this.chatId, usuarioId: this.usuarioId });
                alert('Error: No se pudo identificar el chat o usuario. Por favor, recarga la página.');
                return;
            }
            
            // Si ya hay una llamada activa, no iniciar otra
            if (this.isInCall || this.peerConnection) {
                console.warn('Ya hay una llamada activa');
                return;
            }
            
            // Mostrar modal
            this.mostrarModal();
            
            // Obtener acceso a cámara y micrófono
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            });
            
            // Mostrar video local
            if (this.videoLocal) {
                this.videoLocal.srcObject = this.localStream;
            }
            
            // Crear conexión peer
            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            // Agregar tracks locales
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });
            
            // Configurar handlers
            this.setupPeerConnectionHandlers();
            
            // Crear offer
            this.isInitiator = true;
            this.callId = 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            const offer = await this.peerConnection.createOffer();
            await this.peerConnection.setLocalDescription(offer);
            
            // Enviar offer
            await this.enviarSeñalizacion('offer', {
                type: offer.type,
                sdp: offer.sdp
            });
            
            // Iniciar polling de señalización
            this.iniciarPolling();
            
            this.actualizarEstado('Esperando respuesta...');
            
        } catch (error) {
            console.error('Error al iniciar videollamada:', error);
            alert('Error al iniciar la videollamada: ' + error.message);
            this.endCall();
        }
    }
    
    /**
     * Configurar handlers de la conexión peer
     */
    setupPeerConnectionHandlers() {
        // Cuando recibimos un track remoto
        this.peerConnection.ontrack = (event) => {
            console.log('Track remoto recibido:', event.track.kind);
            this.remoteStream = event.streams[0];
            if (this.videoRemoto) {
                this.videoRemoto.srcObject = this.remoteStream;
            }
            this.isInCall = true;
            this.iniciarTimer();
            this.ocultarEstado();
        };
        
        // Cuando se genera un ICE candidate
        this.peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                this.enviarSeñalizacion('ice-candidate', event.candidate.toJSON());
            }
        };
        
        // Cambios en el estado de conexión
        this.peerConnection.onconnectionstatechange = () => {
            const state = this.peerConnection.connectionState;
            
            switch (state) {
                case 'connected':
                    this.isInCall = true;
                    this.iniciarTimer();
                    this.ocultarEstado();
                    this.detenerPolling(); // Ya no necesitamos polling cuando estamos conectados
                    break;
                case 'disconnected':
                    // Intentar reconectar solo si no fue intencional
                    if (this.isInCall) {
                        this.actualizarEstado('Conexión perdida. Intentando reconectar...');
                        // Reiniciar polling para intentar reconectar
                        if (!this.signalingInterval) {
                            this.iniciarPolling();
                        }
                    }
                    break;
                case 'failed':
                    this.actualizarEstado('Error de conexión. Cerrando...');
                    setTimeout(() => this.endCall(), 3000);
                    break;
                case 'closed':
                    // Solo terminar si no fue intencional
                    if (this.isInCall) {
                        this.endCall();
                    }
                    break;
            }
        };
        
        // Cambios en el estado ICE
        this.peerConnection.oniceconnectionstatechange = () => {
            const iceState = this.peerConnection.iceConnectionState;
            
            switch (iceState) {
                case 'failed':
                    this.actualizarEstado('Error de conexión de red. Verifica tu conexión a internet.');
                    setTimeout(() => this.endCall(), 5000);
                    break;
                case 'disconnected':
                    if (this.isInCall) {
                        this.actualizarEstado('Conexión de red perdida. Intentando reconectar...');
                    }
                    break;
                case 'connected':
                case 'completed':
                    // Conexión establecida correctamente
                    break;
            }
        };
    }
    
    /**
     * Enviar mensaje de señalización
     */
    async enviarSeñalizacion(tipo, datos) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('No se encontró el token CSRF');
                return false;
            }
            
            // Timeout para evitar que se quede colgado
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 segundos
            
            const response = await fetch(`/chats/${this.chatId}/videollamada/señalizacion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tipo: tipo,
                    datos: datos,
                    call_id: this.callId,
                    usuario_id: this.usuarioId
                }),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    console.error('Error de autenticación al enviar señalización');
                    alert('Error de autenticación. Por favor, recarga la página.');
                } else if (response.status === 404) {
                    console.error('Chat o usuario no encontrado');
                    alert('Error: No se encontró el chat. Por favor, recarga la página.');
                } else {
                    console.error('Error al enviar señalización:', response.status, response.statusText);
                }
                return false;
            }
            
            const result = await response.json();
            return result.success === true;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.error('Timeout al enviar señalización');
                return false;
            }
            console.error('Error al enviar señalización:', error);
            return false;
        }
    }
    
    /**
     * Iniciar polling para recibir mensajes de señalización
     */
    iniciarPolling() {
        if (this.signalingInterval) {
            return;
        }
        
        let pollingAttempts = 0;
        const maxPollingAttempts = 300; // 10 minutos máximo para polling continuo (300 * 2 segundos)
        
        console.log('Iniciando polling de señalización...');
        
        this.signalingInterval = setInterval(async () => {
            // Si ya estamos conectados en una llamada, seguir polling pero con menos frecuencia
            // (necesario para recibir ICE candidates y otros mensajes)
            if (this.isInCall && this.peerConnection?.connectionState === 'connected') {
                pollingAttempts = 0; // Resetear contador
                // Continuar polling pero con menos frecuencia (cada 5 segundos en lugar de 2)
                // Esto se maneja con el intervalo existente
            }
            
            pollingAttempts++;
            
            // Limitar tiempo de polling solo si no estamos en llamada
            if (!this.isInCall && pollingAttempts > maxPollingAttempts) {
                console.warn('Polling excedió el tiempo máximo sin llamada activa');
                // No detener el polling, solo resetear el contador
                pollingAttempts = 0;
            }
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    return;
                }
                
                // Timeout para evitar que el polling se quede colgado
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000);
                
                const response = await fetch(`/chats/${this.chatId}/videollamada/señalizacion`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    if (response.status === 401 || response.status === 403) {
                        console.error('Error de autenticación en polling');
                        // No detener polling, puede ser temporal
                    }
                    return;
                }
                
                const result = await response.json();
                if (result.success && result.mensajes && result.mensajes.length > 0) {
                    for (const mensaje of result.mensajes) {
                        // Ignorar mensajes propios
                        if (mensaje.from_usuario_id === this.usuarioId) {
                            continue;
                        }
                        
                        console.log('Mensaje de señalización recibido:', mensaje.tipo);
                        await this.procesarMensajeSeñalizacion(mensaje.tipo, mensaje.datos);
                    }
                    pollingAttempts = 0; // Resetear contador al recibir mensajes
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    // Timeout, continuar
                    return;
                }
                console.error('Error en polling:', error);
            }
        }, 2000); // Polling cada 2 segundos
    }
    
    /**
     * Detener polling
     */
    detenerPolling() {
        if (this.signalingInterval) {
            clearInterval(this.signalingInterval);
            this.signalingInterval = null;
        }
    }
    
    /**
     * Procesar mensaje de señalización recibido
     */
    async procesarMensajeSeñalizacion(tipo, datos) {
        try {
            switch (tipo) {
                case 'offer':
                    console.log('Offer recibido, procesando...');
                    
                    // Si ya somos iniciadores, ignorar
                    if (this.isInitiator) {
                        console.log('Ignorando offer porque ya somos iniciadores');
                        return;
                    }
                    
                    // Si ya estamos en llamada, ignorar
                    if (this.isInCall || this.peerConnection) {
                        console.log('Ignorando offer porque ya hay una llamada activa');
                        return;
                    }
                    
                    // Si ya hay una oferta pendiente, ignorar esta nueva
                    if (this.incomingOffer) {
                        console.log('Ya hay una oferta pendiente, ignorando nueva');
                        return;
                    }
                    
                    console.log('Mostrando notificación de llamada entrante');
                    // Mostrar notificación de llamada entrante
                    this.mostrarLlamadaEntrante(datos);
                    break;
                    
                case 'answer':
                    // Solo procesar si somos iniciadores
                    if (!this.isInitiator || !this.peerConnection) {
                        return;
                    }
                    
                    await this.peerConnection.setRemoteDescription(new RTCSessionDescription(datos));
                    break;
                    
                case 'ice-candidate':
                    if (!this.peerConnection) {
                        return;
                    }
                    
                    try {
                        await this.peerConnection.addIceCandidate(new RTCIceCandidate(datos));
                    } catch (error) {
                        // Ignorar errores si la conexión ya está establecida
                        if (this.peerConnection.connectionState !== 'connected' && 
                            this.peerConnection.connectionState !== 'completed') {
                            console.warn('Error al agregar ICE candidate:', error);
                        }
                    }
                    break;
                    
                case 'call-rejected':
                    this.actualizarEstado('Llamada rechazada.');
                    setTimeout(() => this.endCall(), 3000);
                    break;
            }
        } catch (error) {
            console.error('Error al procesar mensaje de señalización:', error);
        }
    }
    
    /**
     * Mostrar notificación de llamada entrante
     */
    mostrarLlamadaEntrante(offerData) {
        console.log('Mostrando modal de llamada entrante');
        this.incomingOffer = offerData;
        
        // Reproducir sonido de llamada
        this.playCallSound();
        
        // Actualizar nombre del llamante si está disponible
        if (this.nombreLlamante) {
            this.nombreLlamante.textContent = 'Llamada entrante...';
        }
        
        if (this.modalLlamadaEntrante) {
            if (typeof bootstrap !== 'undefined') {
                try {
                    // Cerrar cualquier modal existente primero
                    const existingModal = bootstrap.Modal.getInstance(this.modalLlamadaEntrante);
                    if (existingModal) {
                        existingModal.hide();
                    }
                    
                    const modal = new bootstrap.Modal(this.modalLlamadaEntrante, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                    console.log('Modal de llamada entrante mostrado');
                } catch (error) {
                    console.error('Error al mostrar modal con Bootstrap:', error);
                    // Fallback manual
                    this.modalLlamadaEntrante.style.display = 'block';
                    this.modalLlamadaEntrante.classList.add('show');
                }
            } else {
                this.modalLlamadaEntrante.style.display = 'block';
                this.modalLlamadaEntrante.classList.add('show');
            }
        } else {
            console.error('Modal de llamada entrante no encontrado');
        }
    }
    
    /**
     * Aceptar llamada entrante
     */
    async aceptarLlamada() {
        console.log('Aceptando llamada entrante...');
        
        if (!this.incomingOffer) {
            console.error('No hay oferta de llamada para aceptar');
            return;
        }
        
        // Detener sonido de llamada
        this.stopCallSound();
        
        // Cerrar modal de notificación
        if (this.modalLlamadaEntrante) {
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(this.modalLlamadaEntrante);
                if (modal) modal.hide();
            } else {
                this.modalLlamadaEntrante.style.display = 'none';
                this.modalLlamadaEntrante.classList.remove('show');
            }
        }
        
        try {
            // Mostrar modal de videollamada
            this.mostrarModal();
            
            // Obtener acceso a cámara y micrófono con manejo de errores mejorado
            try {
                this.localStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280, max: 1920 },
                        height: { ideal: 720, max: 1080 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                        sampleRate: 48000
                    }
                });
            } catch (mediaError) {
                console.error('Error al acceder a medios:', mediaError);
                
                // Mensajes de error más específicos
                if (mediaError.name === 'NotAllowedError' || mediaError.name === 'PermissionDeniedError') {
                    alert('Se requiere permiso para acceder a la cámara y micrófono. Por favor, permite el acceso en la configuración de tu navegador.');
                } else if (mediaError.name === 'NotFoundError' || mediaError.name === 'DevicesNotFoundError') {
                    alert('No se encontraron cámara o micrófono. Por favor, verifica que estén conectados.');
                } else if (mediaError.name === 'NotReadableError' || mediaError.name === 'TrackStartError') {
                    alert('La cámara o micrófono están siendo usados por otra aplicación. Por favor, ciérrala e intenta de nuevo.');
                } else {
                    alert('Error al acceder a la cámara/micrófono: ' + mediaError.message);
                }
                
                this.endCall();
                return;
            }
            
            // Mostrar video local
            if (this.videoLocal) {
                this.videoLocal.srcObject = this.localStream;
            }
            
            // Crear conexión peer
            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            // Agregar tracks locales
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });
            
            // Configurar handlers
            this.setupPeerConnectionHandlers();
            
            // Procesar offer recibido
            await this.peerConnection.setRemoteDescription(new RTCSessionDescription(this.incomingOffer));
            
            // Crear answer
            const answer = await this.peerConnection.createAnswer();
            await this.peerConnection.setLocalDescription(answer);
            
            // Enviar answer
            await this.enviarSeñalizacion('answer', {
                type: answer.type,
                sdp: answer.sdp
            });
            
            // Iniciar polling
            this.iniciarPolling();
            
            this.isInitiator = false;
            this.incomingOffer = null;
            
        } catch (error) {
            console.error('Error al aceptar llamada:', error);
            alert('Error al aceptar la llamada: ' + error.message);
            this.endCall();
        }
    }
    
    /**
     * Rechazar llamada entrante
     */
    async rechazarLlamada() {
        console.log('Rechazando llamada entrante...');
        
        // Detener sonido de llamada
        this.stopCallSound();
        
        if (this.incomingOffer) {
            await this.enviarSeñalizacion('call-rejected', {});
            this.incomingOffer = null;
        }
        
        // Cerrar modal
        if (this.modalLlamadaEntrante) {
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(this.modalLlamadaEntrante);
                if (modal) modal.hide();
            } else {
                this.modalLlamadaEntrante.style.display = 'none';
                this.modalLlamadaEntrante.classList.remove('show');
            }
        }
    }
    
    /**
     * Toggle audio (mute/unmute)
     */
    toggleAudio() {
        if (this.localStream) {
            const audioTrack = this.localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                this.isAudioMuted = !audioTrack.enabled;
                
                if (this.btnToggleAudio) {
                    if (this.isAudioMuted) {
                        this.btnToggleAudio.innerHTML = '<i class="fa fa-microphone-slash"></i>';
                        this.btnToggleAudio.classList.add('btn-danger');
                    } else {
                        this.btnToggleAudio.innerHTML = '<i class="fa fa-microphone"></i>';
                        this.btnToggleAudio.classList.remove('btn-danger');
                    }
                }
            }
        }
    }
    
    /**
     * Toggle video (on/off)
     */
    toggleVideo() {
        if (this.localStream) {
            const videoTrack = this.localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                this.isVideoMuted = !videoTrack.enabled;
                
                if (this.btnToggleVideo) {
                    if (this.isVideoMuted) {
                        this.btnToggleVideo.innerHTML = '<i class="fa fa-video-slash"></i>';
                        this.btnToggleVideo.classList.add('btn-danger');
                    } else {
                        this.btnToggleVideo.innerHTML = '<i class="fa fa-video-camera"></i>';
                        this.btnToggleVideo.classList.remove('btn-danger');
                    }
                }
            }
        }
    }
    
    /**
     * Terminar llamada
     */
    endCall() {
        this.isInCall = false;
        this.detenerTimer();
        this.stopCallSound();
        
        // Cerrar conexión peer
        if (this.peerConnection) {
            this.peerConnection.close();
            this.peerConnection = null;
        }
        
        // Detener streams locales
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }
        
        // Limpiar videos
        if (this.videoLocal) {
            this.videoLocal.srcObject = null;
        }
        if (this.videoRemoto) {
            this.videoRemoto.srcObject = null;
        }
        
        // Cerrar modales
        if (this.modalVideollamada) {
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(this.modalVideollamada);
                if (modal) modal.hide();
            } else {
                this.modalVideollamada.style.display = 'none';
                this.modalVideollamada.classList.remove('show');
            }
        }
        
        if (this.modalLlamadaEntrante) {
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(this.modalLlamadaEntrante);
                if (modal) modal.hide();
            } else {
                this.modalLlamadaEntrante.style.display = 'none';
                this.modalLlamadaEntrante.classList.remove('show');
            }
        }
        
        // Resetear estado
        const wasInCall = this.isInCall;
        this.isInitiator = false;
        this.callId = null;
        this.incomingOffer = null;
        this.isInCall = false;
        
        // Reiniciar polling si estábamos en llamada (para detectar nuevas llamadas)
        // El polling debe continuar activo para detectar llamadas entrantes
        if (wasInCall && !this.signalingInterval) {
            this.iniciarPolling();
        }
        
        // Resetear botones
        if (this.btnToggleAudio) {
            this.btnToggleAudio.innerHTML = '<i class="fa fa-microphone"></i>';
            this.btnToggleAudio.classList.remove('btn-danger');
        }
        if (this.btnToggleVideo) {
            this.btnToggleVideo.innerHTML = '<i class="fa fa-video-camera"></i>';
            this.btnToggleVideo.classList.remove('btn-danger');
        }
        
        this.ocultarEstado();
    }
    
    /**
     * Mostrar modal de videollamada
     */
    mostrarModal() {
        if (this.modalVideollamada) {
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(this.modalVideollamada, {
                    backdrop: false,
                    keyboard: true
                });
                modal.show();
            } else {
                this.modalVideollamada.style.display = 'block';
                this.modalVideollamada.classList.add('show');
            }
        }
    }
    
    /**
     * Actualizar estado de la videollamada
     */
    actualizarEstado(mensaje) {
        if (this.estadoVideollamada) {
            this.estadoVideollamada.innerHTML = `
                <i class="fa fa-spinner fa-spin fa-3x mb-3"></i>
                <h5>${mensaje}</h5>
            `;
            this.estadoVideollamada.style.display = 'block';
        }
    }
    
    /**
     * Ocultar estado
     */
    ocultarEstado() {
        if (this.estadoVideollamada) {
            this.estadoVideollamada.style.display = 'none';
        }
    }
    
    /**
     * Iniciar timer de llamada
     */
    iniciarTimer() {
        this.callStartTime = Date.now();
        this.callTimer = setInterval(() => {
            if (this.tiempoVideollamada) {
                const elapsed = Math.floor((Date.now() - this.callStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                this.tiempoVideollamada.textContent = 
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    }
    
    /**
     * Detener timer
     */
    detenerTimer() {
        if (this.callTimer) {
            clearInterval(this.callTimer);
            this.callTimer = null;
        }
        if (this.tiempoVideollamada) {
            this.tiempoVideollamada.textContent = '00:00';
        }
    }
}

// Exportar para uso global
window.VideoCall = VideoCall;