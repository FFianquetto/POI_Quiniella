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
            
            // Inicializar stream remoto antes de crear la conexión
            this.remoteStream = new MediaStream();
            
            // Crear conexión peer
            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            // Agregar tracks locales
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
                console.log('Track local agregado:', track.kind, track.id);
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
        // Crear un stream remoto combinado para todos los tracks
        if (!this.remoteStream) {
            this.remoteStream = new MediaStream();
        }
        
        // Cuando recibimos un track remoto
        this.peerConnection.ontrack = (event) => {
            console.log('Track remoto recibido:', {
                kind: event.track.kind,
                id: event.track.id,
                enabled: event.track.enabled,
                readyState: event.track.readyState,
                streams: event.streams.length
            });
            
            // Agregar el track al stream remoto combinado
            if (event.track && !this.remoteStream.getTracks().find(t => t.id === event.track.id)) {
                this.remoteStream.addTrack(event.track);
                console.log('Track agregado al stream remoto:', event.track.kind);
            }
            
            // Si el stream viene en el evento, también agregar esos tracks
            if (event.streams && event.streams.length > 0) {
                event.streams[0].getTracks().forEach(track => {
                    if (!this.remoteStream.getTracks().find(t => t.id === track.id)) {
                        this.remoteStream.addTrack(track);
                        console.log('Track del stream agregado:', track.kind);
                    }
                });
            }
            
            // Asignar el stream al elemento de video
            if (this.videoRemoto) {
                this.videoRemoto.srcObject = this.remoteStream;
                
                // Forzar reproducción del video
                this.videoRemoto.play().catch(error => {
                    console.warn('Error al reproducir video remoto:', error);
                });
                
                // Agregar listener para cuando el video esté listo
                this.videoRemoto.onloadedmetadata = () => {
                    console.log('Metadata del video remoto cargada');
                    this.videoRemoto.play().catch(error => {
                        console.warn('Error al reproducir después de metadata:', error);
                    });
                };
            }
            
            // Log del estado del stream
            console.log('Stream remoto actual:', {
                videoTracks: this.remoteStream.getVideoTracks().length,
                audioTracks: this.remoteStream.getAudioTracks().length,
                totalTracks: this.remoteStream.getTracks().length
            });
            
            // Iniciar timer y actualizar estado solo si es la primera vez
            if (!this.isInCall) {
                this.isInCall = true;
                this.iniciarTimer();
                this.ocultarEstado();
            }
            
            // Listener para cuando el track cambie de estado
            event.track.onended = () => {
                console.log('Track remoto terminado:', event.track.kind);
            };
            
            event.track.onmute = () => {
                console.log('Track remoto silenciado:', event.track.kind);
            };
            
            event.track.onunmute = () => {
                console.log('Track remoto activado:', event.track.kind);
                // Asegurar que el video se muestre cuando el track se active
                if (event.track.kind === 'video' && this.videoRemoto) {
                    this.videoRemoto.play().catch(error => {
                        console.warn('Error al reproducir video después de unmute:', error);
                    });
                }
            };
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
                    console.log('Conexión WebRTC establecida');
                    this.isInCall = true;
                    this.iniciarTimer();
                    this.ocultarEstado();
                    this.detenerPolling(); // Ya no necesitamos polling cuando estamos conectados
                    
                    // Verificar que los tracks remotos estén disponibles
                    if (this.remoteStream) {
                        const videoTracks = this.remoteStream.getVideoTracks();
                        const audioTracks = this.remoteStream.getAudioTracks();
                        console.log('Tracks remotos después de conexión:', {
                            video: videoTracks.length,
                            audio: audioTracks.length
                        });
                        
                        // Si hay tracks pero el video no se muestra, forzar actualización
                        if (videoTracks.length > 0 && this.videoRemoto) {
                            this.videoRemoto.srcObject = this.remoteStream;
                            this.videoRemoto.play().catch(error => {
                                console.warn('Error al reproducir video después de conexión:', error);
                            });
                        }
                    }
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
                    console.log('ICE connection state:', iceState);
                    if (iceState === 'completed') {
                        // Verificar tracks remotos cuando la conexión ICE se complete
                        if (this.remoteStream) {
                            const videoTracks = this.remoteStream.getVideoTracks();
                            const audioTracks = this.remoteStream.getAudioTracks();
                            console.log('Tracks remotos cuando ICE completado:', {
                                video: videoTracks.length,
                                audio: audioTracks.length,
                                videoEnabled: videoTracks.length > 0 ? videoTracks[0].enabled : false,
                                audioEnabled: audioTracks.length > 0 ? audioTracks[0].enabled : false
                            });
                            
                            // Asegurar que el video se muestre
                            if (videoTracks.length > 0 && this.videoRemoto) {
                                this.videoRemoto.srcObject = this.remoteStream;
                                this.videoRemoto.play().catch(error => {
                                    console.warn('Error al reproducir video cuando ICE completado:', error);
                                });
                            }
                        }
                    }
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
                    
                    // Limpiar y normalizar el SDP antes de procesarlo
                    const cleanedAnswer = this.limpiarSessionDescription(datos);
                    try {
                        await this.peerConnection.setRemoteDescription(new RTCSessionDescription(cleanedAnswer));
                    } catch (sdpError) {
                        console.error('Error al procesar SDP de answer:', sdpError);
                        // Intentar limpieza más agresiva
                        if (cleanedAnswer.sdp) {
                            let sdpLines = cleanedAnswer.sdp.split('\r\n');
                            sdpLines = sdpLines.filter(line => {
                                if (line.startsWith('a=ssrc:')) {
                                    const msidIndex = line.indexOf('msid:');
                                    if (msidIndex > 0) {
                                        const afterMsid = line.substring(msidIndex + 5);
                                        const parts = afterMsid.trim().split(/\s+/);
                                        if (parts.length > 2) {
                                            return false;
                                        }
                                    }
                                }
                                return true;
                            });
                            
                            const moreCleanedAnswer = {
                                type: cleanedAnswer.type,
                                sdp: sdpLines.join('\r\n') + '\r\n'
                            };
                            
                            await this.peerConnection.setRemoteDescription(new RTCSessionDescription(moreCleanedAnswer));
                        } else {
                            throw sdpError;
                        }
                    }
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
     * Normalizar y limpiar SDP para evitar errores de parsing
     * El SDP puede corromperse durante la serialización JSON
     */
    normalizarSDP(sdpString) {
        if (!sdpString || typeof sdpString !== 'string') {
            return sdpString;
        }
        
        // Dividir en líneas y limpiar
        let lineas = sdpString.split(/\r?\n/);
        const lineasLimpias = [];
        
        for (let i = 0; i < lineas.length; i++) {
            let linea = lineas[i].trim();
            
            // Saltar líneas vacías
            if (!linea) {
                continue;
            }
            
            // Detectar y corregir líneas a=ssrc con múltiples msid en la misma línea
            // Problema común: a=ssrc:ID msid:UUID1 UUID2 (dos UUIDs después de msid:)
            if (linea.startsWith('a=ssrc:') && linea.includes('msid:')) {
                const ssrcMatch = linea.match(/^a=ssrc:(\d+)/);
                if (ssrcMatch) {
                    const ssrcId = ssrcMatch[1];
                    let lineaCorregida = linea;
                    let necesitaCorreccion = false;
                    
                    // Buscar la posición de msid:
                    const msidIndex = linea.indexOf('msid:');
                    if (msidIndex > 0) {
                        const parteAntes = linea.substring(0, msidIndex + 5); // "a=ssrc:ID msid:"
                        const parteDespues = linea.substring(msidIndex + 5).trim(); // Todo después de "msid:"
                        
                        // Dividir por espacios para ver si hay múltiples valores
                        const valores = parteDespues.split(/\s+/);
                        
                        // Detectar si hay múltiples UUIDs (formato UUID: 8-4-4-4-12 caracteres hex)
                        const uuidPattern = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
                        const valoresUUID = valores.filter(v => uuidPattern.test(v));
                        
                        if (valoresUUID.length > 1) {
                            // Hay múltiples UUIDs, tomar solo el primero
                            console.warn('SDP: Detectado múltiples msid en la misma línea, usando solo el primero:', linea);
                            lineaCorregida = `${parteAntes}${valoresUUID[0]}`;
                            necesitaCorreccion = true;
                        } else if (valores.length > 1 && !valores[1].match(/^[a-z]=/)) {
                            // Hay múltiples valores pero no son UUIDs válidos, puede ser corrupción
                            // Tomar solo el primer valor que parezca un UUID o identificador válido
                            const primerValor = valores[0];
                            if (primerValor && primerValor.length > 0) {
                                console.warn('SDP: Detectado formato inválido en línea msid, limpiando:', linea);
                                lineaCorregida = `${parteAntes}${primerValor}`;
                                necesitaCorreccion = true;
                            }
                        }
                    }
                    
                    // También verificar si hay múltiples ocurrencias de "msid:" en la línea
                    const msidMatches = lineaCorregida.match(/msid:/g);
                    if (msidMatches && msidMatches.length > 1) {
                        // Hay múltiples msid:, separarlos en líneas diferentes
                        const msidParts = lineaCorregida.split('msid:');
                        for (let j = 1; j < msidParts.length; j++) {
                            const msidValue = msidParts[j].trim().split(/\s+/)[0];
                            if (msidValue) {
                                lineasLimpias.push(`a=ssrc:${ssrcId} msid:${msidValue}`);
                            }
                        }
                        continue;
                    }
                    
                    // Si se corrigió la línea, usar la versión corregida
                    if (necesitaCorreccion) {
                        lineasLimpias.push(lineaCorregida);
                        continue;
                    }
                }
            }
            
            // Detectar líneas que pueden tener caracteres inválidos o saltos de línea incorrectos
            // Si una línea parece estar dividida incorrectamente (empieza con espacio pero no es continuación)
            if (linea.match(/^[a-z]=/) || linea.match(/^[a-z]:/)) {
                // Es una línea válida de SDP
                lineasLimpias.push(linea);
            } else if (linea.match(/^[a-z]/)) {
                // Línea que empieza con letra minúscula (puede ser continuación de línea anterior)
                // Verificar si la línea anterior necesita esta continuación
                if (lineasLimpias.length > 0) {
                    const ultimaLinea = lineasLimpias[lineasLimpias.length - 1];
                    // Si la última línea es una línea de atributo que puede tener continuación
                    if (ultimaLinea.startsWith('a=') && !ultimaLinea.endsWith('\\')) {
                        // Puede ser una línea separada incorrectamente, unirla
                        lineasLimpias[lineasLimpias.length - 1] = ultimaLinea + ' ' + linea;
                        continue;
                    }
                }
                lineasLimpias.push(linea);
            } else {
                // Otras líneas (pueden ser comentarios o líneas especiales)
                lineasLimpias.push(linea);
            }
        }
        
        // Unir las líneas con \r\n (formato estándar SDP)
        return lineasLimpias.join('\r\n') + '\r\n';
    }
    
    /**
     * Validar y limpiar RTCSessionDescription antes de usarlo
     */
    limpiarSessionDescription(sessionDescription) {
        if (!sessionDescription || !sessionDescription.sdp) {
            return sessionDescription;
        }
        
        // Crear una copia del objeto
        const cleaned = {
            type: sessionDescription.type,
            sdp: this.normalizarSDP(sessionDescription.sdp)
        };
        
        return cleaned;
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
            
            // Inicializar stream remoto antes de crear la conexión
            this.remoteStream = new MediaStream();
            
            // Crear conexión peer
            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            // Agregar tracks locales
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
                console.log('Track local agregado:', track.kind, track.id);
            });
            
            // Configurar handlers
            this.setupPeerConnectionHandlers();
            
            // Limpiar y normalizar el SDP antes de procesarlo
            const cleanedOffer = this.limpiarSessionDescription(this.incomingOffer);
            console.log('SDP limpiado, procesando offer...');
            
            // Procesar offer recibido con manejo de errores mejorado
            try {
                await this.peerConnection.setRemoteDescription(new RTCSessionDescription(cleanedOffer));
            } catch (sdpError) {
                console.error('Error al procesar SDP:', sdpError);
                console.error('SDP original:', this.incomingOffer);
                console.error('SDP limpiado:', cleanedOffer);
                
                // Intentar una limpieza más agresiva
                if (cleanedOffer.sdp) {
                    // Aplicar normalización nuevamente con parámetros más estrictos
                    let sdpLines = cleanedOffer.sdp.split(/\r?\n/);
                    const lineasCorregidas = [];
                    
                    for (const line of sdpLines) {
                        const linea = line.trim();
                        if (!linea) continue;
                        
                        // Corregir líneas a=ssrc problemáticas
                        if (linea.startsWith('a=ssrc:') && linea.includes('msid:')) {
                            const ssrcMatch = linea.match(/^a=ssrc:(\d+)/);
                            if (ssrcMatch) {
                                const ssrcId = ssrcMatch[1];
                                const msidIndex = linea.indexOf('msid:');
                                if (msidIndex > 0) {
                                    const parteAntes = linea.substring(0, msidIndex + 5);
                                    const parteDespues = linea.substring(msidIndex + 5).trim();
                                    const valores = parteDespues.split(/\s+/);
                                    
                                    // Si hay más de un valor y el segundo parece un UUID, tomar solo el primero
                                    if (valores.length > 1) {
                                        const uuidPattern = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
                                        const primerValor = valores[0];
                                        const segundoValor = valores[1];
                                        
                                        // Si el segundo valor es un UUID, es un msid duplicado inválido
                                        if (uuidPattern.test(segundoValor)) {
                                            console.warn('SDP: Limpieza agresiva - removiendo msid duplicado:', linea);
                                            lineasCorregidas.push(`${parteAntes}${primerValor}`);
                                            continue;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Mantener la línea si no es problemática
                        lineasCorregidas.push(linea);
                    }
                    
                    const moreCleanedOffer = {
                        type: cleanedOffer.type,
                        sdp: lineasCorregidas.join('\r\n') + '\r\n'
                    };
                    
                    console.log('Intentando con SDP más limpiado...');
                    try {
                        await this.peerConnection.setRemoteDescription(new RTCSessionDescription(moreCleanedOffer));
                    } catch (finalError) {
                        console.error('Error incluso después de limpieza agresiva:', finalError);
                        throw new Error(`Error al procesar SDP: ${finalError.message}. El SDP puede estar corrupto.`);
                    }
                } else {
                    throw sdpError;
                }
            }
            
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
        
        // Detener y limpiar stream remoto
        if (this.remoteStream) {
            this.remoteStream.getTracks().forEach(track => {
                track.stop();
                this.remoteStream.removeTrack(track);
            });
            this.remoteStream = null;
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