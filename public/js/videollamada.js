class VideoCall {
    constructor(chatId, usuarioId) {
        this.chatId = chatId;
        this.usuarioId = usuarioId;
        this.peerConnection = null;
        this.localStream = null;
        this.remoteStream = null;
        this.isInCall = false;
        this.callStartTime = null;
        this.callTimer = null;
        this.isAudioMuted = false;
        this.isVideoMuted = false;
        this.callId = null;
        this.isInitiator = false;
        this.signalingInterval = null;
        this.signalingEventSource = null;
        this.incomingCallOffer = null;
        this.callNotificationAudio = null;
        this.isRinging = false;
        
        // Configuración mejorada de ICE servers para producción
        // Incluye múltiples STUN servers y TURN servers públicos
        this.configuration = {
            iceServers: [
                // STUN servers de Google (gratuitos)
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' },
                { urls: 'stun:stun3.l.google.com:19302' },
                { urls: 'stun:stun4.l.google.com:19302' },
                // STUN servers alternativos
                { urls: 'stun:stun.stunprotocol.org:3478' },
                { urls: 'stun:stun.voiparound.com' },
                { urls: 'stun:stun.voipbuster.com' },
                { urls: 'stun:stun.voipstunt.com' },
                // TURN servers públicos (gratuitos, pueden tener limitaciones)
                // Nota: Para producción real, se recomienda usar TURN servers propios
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
            iceCandidatePoolSize: 10
        };
        
        this.initializeElements();
        this.bindEvents();
        this.initializeCallNotification();
        
        // Iniciar escucha de llamadas entrantes automáticamente
        this.startSignalingPolling();
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
        
        // Elementos de notificación de llamada entrante
        this.modalLlamadaEntrante = document.getElementById('modalLlamadaEntrante');
        this.btnAceptarLlamada = document.getElementById('btn-aceptar-llamada');
        this.btnRechazarLlamada = document.getElementById('btn-rechazar-llamada');
        this.nombreLlamante = document.getElementById('nombre-llamante');
    }
    
    initializeCallNotification() {
        // Crear audio para notificación de llamada (usando Web Audio API)
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.callNotificationAudio = audioContext;
        } catch (e) {
            console.warn('No se pudo crear AudioContext para notificaciones');
        }
    }
    
    playCallSound() {
        if (!this.callNotificationAudio) return;
        
        try {
            const oscillator = this.callNotificationAudio.createOscillator();
            const gainNode = this.callNotificationAudio.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.callNotificationAudio.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, this.callNotificationAudio.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.callNotificationAudio.currentTime + 0.5);
            
            oscillator.start(this.callNotificationAudio.currentTime);
            oscillator.stop(this.callNotificationAudio.currentTime + 0.5);
            
            // Repetir cada segundo
            if (this.isRinging) {
                setTimeout(() => this.playCallSound(), 1000);
            }
        } catch (e) {
            console.warn('Error al reproducir sonido de llamada:', e);
        }
    }
    
    stopCallSound() {
        this.isRinging = false;
    }
    
    showIncomingCallNotification(offerData) {
        // Limpiar el SDP del offer antes de guardarlo
        // El offerData puede venir como RTCSessionDescription o como objeto {type, sdp}
        let cleanedOffer = offerData;
        
        if (offerData) {
            if (offerData.sdp && typeof offerData.sdp === 'string') {
                // Si tiene sdp como string, limpiarlo
                cleanedOffer = {
                    type: offerData.type || 'offer',
                    sdp: this.cleanSDP(offerData.sdp)
                };
            } else if (offerData.type && offerData.sdp) {
                // Si ya tiene type y sdp, limpiar el sdp
                cleanedOffer = {
                    type: offerData.type,
                    sdp: this.cleanSDP(offerData.sdp)
                };
            } else {
                // Si es un RTCSessionDescription, convertir a objeto y limpiar
                try {
                    cleanedOffer = {
                        type: offerData.type,
                        sdp: this.cleanSDP(offerData.sdp || '')
                    };
                } catch (e) {
                    console.error('Error al procesar offerData:', e);
                    cleanedOffer = offerData;
                }
            }
        }
        
        this.incomingCallOffer = cleanedOffer;
        this.isRinging = true;
        
        // Mostrar modal de notificación
        if (this.modalLlamadaEntrante) {
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(this.modalLlamadaEntrante, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            } else {
                this.modalLlamadaEntrante.style.display = 'block';
                this.modalLlamadaEntrante.classList.add('show');
            }
        }
        
        // Reproducir sonido de llamada
        this.playCallSound();
        
        // Obtener nombre del llamante (si está disponible)
        if (this.nombreLlamante) {
            this.nombreLlamante.textContent = 'Llamada entrante...';
        }
    }
    
    async acceptIncomingCall() {
        this.stopCallSound();
        
        if (!this.incomingCallOffer) {
            console.error('No hay oferta de llamada para aceptar');
            return;
        }
        
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
        
        // Iniciar la videollamada como receptor
        await this.answerIncomingCall(this.incomingCallOffer);
    }
    
    async rejectIncomingCall() {
        this.stopCallSound();
        
        // Enviar mensaje de rechazo al otro usuario
        if (this.incomingCallOffer) {
            await this.sendSignalingMessage('call-rejected', {
                reason: 'rejected',
                timestamp: new Date().toISOString()
            });
            
            // Registrar llamada perdida en el chat
            await this.registrarLlamadaPerdida();
        }
        
        this.incomingCallOffer = null;
        
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
    }
    
    async registrarLlamadaPerdida() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('No se encontró el token CSRF');
                return;
            }
            
            const response = await fetch(`/chats/${this.chatId}/videollamada/perdida`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            if (!response.ok) {
                console.error('Error al registrar llamada perdida:', response.statusText);
                return;
            }
            
            const result = await response.json();
            if (result.success) {
                console.log('Llamada perdida registrada en el chat');
                // Recargar la página para mostrar el nuevo mensaje
                // O mejor aún, agregar el mensaje dinámicamente al chat
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        } catch (error) {
            console.error('Error al registrar llamada perdida:', error);
        }
    }
    
    async answerIncomingCall(offerData) {
        try {
            // Validar HTTPS
            if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                alert('Las videollamadas requieren HTTPS en producción.');
                return;
            }
            
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('WebRTC no está soportado en este navegador');
            }
            
            // Mostrar modal de videollamada
            if (typeof bootstrap !== 'undefined' && this.modalVideollamada) {
                const modal = new bootstrap.Modal(this.modalVideollamada, {
                    backdrop: false,
                    keyboard: true
                });
                modal.show();
            } else {
                this.showModalManually();
            }
            
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
            
            if (this.videoLocal) {
                this.videoLocal.srcObject = this.localStream;
            }
            
            // Crear conexión peer
            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });
            
            this.setupPeerConnectionHandlers();
            
            // Procesar el offer recibido - asegurar formato correcto
            let offerDataFormatted;
            
            // Si offerData ya tiene type y sdp, usarlo directamente
            if (offerData.type && offerData.sdp) {
                offerDataFormatted = {
                    type: offerData.type,
                    sdp: this.cleanSDP(offerData.sdp)
                };
            } else if (typeof offerData === 'string') {
                // Si es un string, intentar parsearlo
                try {
                    const parsed = JSON.parse(offerData);
                    offerDataFormatted = {
                        type: parsed.type,
                        sdp: this.cleanSDP(parsed.sdp)
                    };
                } catch (e) {
                    throw new Error('El formato del offer es inválido');
                }
            } else {
                // Intentar extraer type y sdp del objeto
                offerDataFormatted = {
                    type: offerData.type || 'offer',
                    sdp: this.cleanSDP(offerData.sdp || '')
                };
            }
            
            if (!offerDataFormatted.sdp || typeof offerDataFormatted.sdp !== 'string' || offerDataFormatted.sdp.trim().length === 0) {
                console.error('Offer recibido:', offerData);
                throw new Error('El SDP del offer está vacío o inválido');
            }
            
            if (!offerDataFormatted.type || !['offer', 'answer', 'pranswer', 'rollback'].includes(offerDataFormatted.type)) {
                throw new Error('El tipo del offer es inválido');
            }
            
            console.log('Procesando offer con tipo:', offerDataFormatted.type, 'SDP length:', offerDataFormatted.sdp.length);
            
            // LIMPIAR el SDP justo antes de usarlo (por si acaso no se limpió antes)
            const originalSdpLength = offerDataFormatted.sdp.length;
            offerDataFormatted.sdp = this.cleanSDP(offerDataFormatted.sdp);
            const cleanedSdpLength = offerDataFormatted.sdp.length;
            
            if (originalSdpLength !== cleanedSdpLength) {
                console.log(`SDP limpiado antes de procesar: ${originalSdpLength} -> ${cleanedSdpLength} caracteres`);
            }
            
            // Verificar que no queden líneas problemáticas
            const sdpLines = offerDataFormatted.sdp.split('\n');
            const remainingProblematic = sdpLines.filter(line => {
                const trimmed = line.trim();
                return trimmed.startsWith('a=msid:') || 
                       trimmed.startsWith('a=cname:') || 
                       trimmed.startsWith('a=ssrc:') || 
                       trimmed.startsWith('a=ssrc-group:') ||
                       trimmed.startsWith('a=label:');
            });
            
            if (remainingProblematic.length > 0) {
                console.error('ADVERTENCIA: Aún quedan líneas problemáticas después de limpiar:', remainingProblematic);
                // Limpiar nuevamente si quedan líneas problemáticas
                offerDataFormatted.sdp = this.cleanSDP(offerDataFormatted.sdp);
            }
            
            try {
                await this.peerConnection.setRemoteDescription(new RTCSessionDescription(offerDataFormatted));
            } catch (error) {
                console.error('Error al establecer remote description:', error);
                console.error('SDP recibido (primeras 500 chars):', offerDataFormatted.sdp.substring(0, 500));
                console.error('Líneas problemáticas que aún quedan:', remainingProblematic);
                throw error;
            }
            
            // Crear y enviar answer
            const answer = await this.peerConnection.createAnswer();
            await this.peerConnection.setLocalDescription(answer);
            await this.sendSignalingMessage('answer', answer);
            
            this.startSignalingPolling();
            
        } catch (error) {
            console.error('Error al aceptar llamada:', error);
            alert('Error al aceptar la llamada: ' + error.message);
            this.endCall();
        }
    }
    
    bindEvents() {
        if (this.btnVideollamada) {
            this.btnVideollamada.addEventListener('click', () => this.iniciarVideollamada());
        }
        
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
        
        if (this.btnAceptarLlamada) {
            this.btnAceptarLlamada.addEventListener('click', () => this.acceptIncomingCall());
        }
        
        if (this.btnRechazarLlamada) {
            this.btnRechazarLlamada.addEventListener('click', () => this.rejectIncomingCall());
        }
        
        if (this.modalVideollamada) {
            this.modalVideollamada.addEventListener('hidden.bs.modal', () => this.endCall());
            this.modalVideollamada.addEventListener('hide.bs.modal', () => this.endCall());
        }
    }
    
    async iniciarVideollamada() {
        try {
            // Validar que estamos en HTTPS en producción
            if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                alert('Las videollamadas requieren HTTPS en producción. Por favor, accede al sitio usando HTTPS.');
                return;
            }
            
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('WebRTC no está soportado en este navegador. Por favor, usa un navegador moderno como Chrome, Firefox, Edge o Safari.');
            }
            
            if (typeof bootstrap !== 'undefined' && this.modalVideollamada) {
                try {
                    const modal = new bootstrap.Modal(this.modalVideollamada, {
                        backdrop: false,
                        keyboard: true
                    });
                    modal.show();
                } catch (bootstrapError) {
                    console.error('Error al mostrar modal con Bootstrap:', bootstrapError);
                    this.showModalManually();
                }
            } else {
                this.showModalManually();
            }
            
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
            
            if (this.videoLocal) {
                this.videoLocal.srcObject = this.localStream;
            }
            
            if (this.estadoVideollamada) {
                this.estadoVideollamada.style.display = 'block';
                this.estadoVideollamada.innerHTML = `
                    <i class="fa fa-spinner fa-spin fa-3x mb-3"></i>
                    <h5>Iniciando videollamada...</h5>
                    <p>Esperando respuesta del otro usuario</p>
                `;
            }
            
            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });
            
            this.setupPeerConnectionHandlers();
            
            this.isInitiator = true;
            this.callId = 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            const offer = await this.peerConnection.createOffer();
            await this.peerConnection.setLocalDescription(offer);
            
            await this.sendSignalingMessage('offer', offer);
            
            this.startSignalingPolling();
            
        } catch (error) {
            alert('Error al acceder a la cámara/micrófono: ' + error.message);
            this.endCall();
        }
    }
    
    setupPeerConnectionHandlers() {
        if (!this.peerConnection) return;
        
        this.peerConnection.ontrack = (event) => {
            console.log('Track recibido:', event.track.kind);
            this.remoteStream = event.streams[0];
            if (this.videoRemoto) {
                this.videoRemoto.srcObject = this.remoteStream;
                this.videoRemoto.onloadedmetadata = () => {
                    console.log('Video remoto cargado');
                };
            }
            if (this.estadoVideollamada) {
                this.estadoVideollamada.style.display = 'none';
            }
            this.isInCall = true;
            this.startCallTimer();
        };
        
        this.peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                console.log('ICE candidate generado:', event.candidate.type);
                this.sendSignalingMessage('ice-candidate', event.candidate.toJSON());
            } else {
                console.log('Todos los ICE candidates han sido generados');
            }
        };
        
        this.peerConnection.onicegatheringstatechange = () => {
            console.log('ICE gathering state:', this.peerConnection.iceGatheringState);
        };
        
        this.peerConnection.onconnectionstatechange = () => {
            const state = this.peerConnection.connectionState;
            console.log('Connection state:', state);
            
            if (!this.estadoVideollamada) return;
            
            switch (state) {
                case 'connected':
                    this.estadoVideollamada.style.display = 'none';
                    this.isInCall = true;
                    this.startCallTimer();
                    this.stopSignalingPolling();
                    break;
                case 'disconnected':
                    if (this.estadoVideollamada) {
                        this.estadoVideollamada.innerHTML = `
                            <i class="fa fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                            <h5>Conexión perdida</h5>
                            <p>Intentando reconectar...</p>
                        `;
                        this.estadoVideollamada.style.display = 'block';
                    }
                    break;
                case 'failed':
                    console.error('Conexión WebRTC falló');
                    if (this.estadoVideollamada) {
                        this.estadoVideollamada.innerHTML = `
                            <i class="fa fa-times-circle fa-3x mb-3 text-danger"></i>
                            <h5>Error de conexión</h5>
                            <p>No se pudo establecer la conexión. Por favor, intenta de nuevo.</p>
                        `;
                        this.estadoVideollamada.style.display = 'block';
                    }
                    setTimeout(() => this.endCall(), 3000);
                    break;
                case 'connecting':
                    if (this.estadoVideollamada) {
                        this.estadoVideollamada.innerHTML = `
                            <i class="fa fa-spinner fa-spin fa-3x mb-3"></i>
                            <h5>Conectando...</h5>
                            <p>Estableciendo conexión con el otro usuario</p>
                        `;
                        this.estadoVideollamada.style.display = 'block';
                    }
                    break;
            }
        };
        
        this.peerConnection.oniceconnectionstatechange = () => {
            const iceState = this.peerConnection.iceConnectionState;
            console.log('ICE connection state:', iceState);
            
            switch (iceState) {
                case 'failed':
                    console.error('ICE connection falló');
                    if (this.estadoVideollamada) {
                        this.estadoVideollamada.innerHTML = `
                            <i class="fa fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                            <h5>Problema de conexión</h5>
                            <p>No se pudo establecer la conexión de red. Verifica tu conexión a internet.</p>
                        `;
                        this.estadoVideollamada.style.display = 'block';
                    }
                    setTimeout(() => this.endCall(), 5000);
                    break;
                case 'disconnected':
                    console.warn('ICE connection desconectado');
                    break;
                case 'connected':
                case 'completed':
                    console.log('ICE connection establecida');
                    break;
            }
        };
    }
    
    async sendSignalingMessage(tipo, datos) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('No se encontró el token CSRF');
                return;
            }
            
            // Serializar correctamente el SDP
            let datosSerializados = datos;
            if (datos && typeof datos === 'object') {
                // Si es un RTCSessionDescription, convertir a objeto plano
                if (datos.type && datos.sdp) {
                    datosSerializados = {
                        type: datos.type,
                        sdp: this.cleanSDP(datos.sdp)
                    };
                } else if (datos.toJSON) {
                    // Si tiene método toJSON, usarlo y limpiar el SDP
                    datosSerializados = datos.toJSON();
                    if (datosSerializados.sdp) {
                        datosSerializados.sdp = this.cleanSDP(datosSerializados.sdp);
                    }
                } else {
                    // Serializar normalmente
                    datosSerializados = JSON.parse(JSON.stringify(datos));
                    if (datosSerializados.sdp) {
                        datosSerializados.sdp = this.cleanSDP(datosSerializados.sdp);
                    }
                }
            }
            
            const response = await fetch(`/chats/${this.chatId}/videollamada/señalizacion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tipo: tipo,
                    datos: datosSerializados,
                    call_id: this.callId,
                    usuario_id: this.usuarioId
                })
            });
            
            if (!response.ok) {
                console.error('Error al enviar señalización:', response.statusText);
                return;
            }
            
            const result = await response.json();
            if (!result.success) {
                console.error('Error en respuesta de señalización:', result);
                return;
            }
        } catch (error) {
            console.error('Error al enviar mensaje de señalización:', error);
        }
    }
    
    async handleSignalingMessage(tipo, datos) {
        try {
            switch (tipo) {
                case 'offer':
                    // Si ya somos el iniciador, ignorar este offer
                    if (this.isInitiator) {
                        console.log('Ignorando offer porque ya somos el iniciador');
                        return;
                    }
                    
                    // Si ya estamos en una llamada, ignorar
                    if (this.isInCall || this.peerConnection) {
                        console.log('Ignorando offer porque ya hay una llamada activa');
                        return;
                    }
                    
                    console.log('Llamada entrante detectada');
                    // Mostrar notificación de llamada entrante (no necesita peerConnection todavía)
                    this.showIncomingCallNotification(datos);
                    break;
                    
                case 'answer':
                    // Solo procesar answer si somos el iniciador y tenemos peerConnection
                    if (!this.isInitiator || !this.peerConnection) {
                        console.log('Ignorando answer porque no somos el iniciador o no hay peerConnection');
                        return;
                    }
                    console.log('Recibiendo answer...');
                    // Asegurar que datos tenga el formato correcto
                    const answerData = {
                        type: datos.type || 'answer',
                        sdp: this.cleanSDP(datos.sdp || '')
                    };
                    if (!answerData.sdp || answerData.sdp.trim().length === 0) {
                        throw new Error('El SDP del answer está vacío o inválido');
                    }
                    
                    // Asegurar que el SDP esté limpio
                    const originalAnswerLength = answerData.sdp.length;
                    answerData.sdp = this.cleanSDP(answerData.sdp);
                    const cleanedAnswerLength = answerData.sdp.length;
                    
                    if (originalAnswerLength !== cleanedAnswerLength) {
                        console.log(`Answer SDP limpiado: ${originalAnswerLength} -> ${cleanedAnswerLength} caracteres`);
                    }
                    
                    // Verificar que no queden líneas problemáticas
                    const answerSdpLines = answerData.sdp.split('\n');
                    const remainingProblematic = answerSdpLines.filter(line => {
                        const trimmed = line.trim();
                        return trimmed.startsWith('a=msid:') || 
                               trimmed.startsWith('a=cname:') || 
                               trimmed.startsWith('a=ssrc:') || 
                               trimmed.startsWith('a=ssrc-group:') ||
                               trimmed.startsWith('a=label:');
                    });
                    
                    if (remainingProblematic.length > 0) {
                        console.error('ADVERTENCIA: Aún quedan líneas problemáticas en answer después de limpiar:', remainingProblematic);
                        // Limpiar nuevamente si quedan líneas problemáticas
                        answerData.sdp = this.cleanSDP(answerData.sdp);
                    }
                    
                    try {
                        await this.peerConnection.setRemoteDescription(new RTCSessionDescription(answerData));
                    } catch (error) {
                        console.error('Error al establecer remote description (answer):', error);
                        console.error('SDP recibido (primeras 500 chars):', answerData.sdp.substring(0, 500));
                        console.error('Líneas problemáticas que aún quedan:', remainingProblematic);
                        throw error;
                    }
                    break;
                    
                case 'ice-candidate':
                    if (!this.peerConnection) {
                        console.warn('No hay peerConnection para agregar ICE candidate');
                        return;
                    }
                    if (datos && (datos.candidate || datos.sdpMLineIndex !== undefined)) {
                        try {
                            await this.peerConnection.addIceCandidate(new RTCIceCandidate(datos));
                            console.log('ICE candidate agregado exitosamente');
                        } catch (error) {
                            // Ignorar errores si la conexión ya está establecida
                            if (this.peerConnection.connectionState !== 'connected' && 
                                this.peerConnection.connectionState !== 'completed') {
                                console.warn('Error al agregar ICE candidate:', error);
                            }
                        }
                    }
                    break;
                    
                case 'call-rejected':
                    // Llamada rechazada por el otro usuario
                    console.log('Llamada rechazada por el otro usuario');
                    this.showCallRejectedNotification();
                    this.endCall();
                    break;
            }
        } catch (error) {
            console.error('Error al procesar mensaje de señalización:', error);
            // Si es error de SDP, mostrar mensaje más claro
            if (error.message && error.message.includes('SessionDescription')) {
                console.error('Error de SDP:', error);
                if (this.estadoVideollamada) {
                    this.estadoVideollamada.innerHTML = `
                        <i class="fa fa-exclamation-triangle fa-3x mb-3 text-danger"></i>
                        <h5>Error de conexión</h5>
                        <p>No se pudo establecer la conexión. Por favor, intenta de nuevo.</p>
                    `;
                    this.estadoVideollamada.style.display = 'block';
                }
                setTimeout(() => this.endCall(), 3000);
            }
        }
    }
    
    showCallRejectedNotification() {
        // Mostrar notificación de llamada perdida/rechazada
        if (this.estadoVideollamada) {
            this.estadoVideollamada.innerHTML = `
                <i class="fa fa-phone-slash fa-3x mb-3 text-danger"></i>
                <h5>Llamada perdida</h5>
                <p>El usuario no pudo atender la llamada</p>
            `;
            this.estadoVideollamada.style.display = 'block';
        }
        
        // Cerrar automáticamente después de 3 segundos
        setTimeout(() => {
            this.endCall();
        }, 3000);
    }
    
    /**
     * Limpiar y validar el SDP antes de usarlo
     * Elimina líneas opcionales problemáticas que causan errores de parsing
     */
    cleanSDP(sdp) {
        if (!sdp || typeof sdp !== 'string') {
            return '';
        }
        
        // Normalizar saltos de línea
        sdp = sdp.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        
        // Dividir en líneas
        const lines = sdp.split('\n');
        const cleanedLines = [];
        
        // Líneas opcionales que causan problemas y pueden eliminarse completamente
        // Estas líneas NO son necesarias para establecer la conexión WebRTC
        // IMPORTANTE: NO eliminar a=mid: porque es necesario para identificar medios
        const problematicLines = [
            'a=msid:',          // Identificación de streams (opcional)
            'a=cname:',         // Nombre de cámara (opcional)
            'a=ssrc-group:',    // Agrupación de SSRC (opcional, causa problemas)
            'a=label:',         // Etiquetas (opcional)
        ];
        
        for (let line of lines) {
            // Eliminar espacios al inicio y final
            line = line.trim();
            
            // Saltar líneas vacías
            if (!line) {
                continue;
            }
            
            // Eliminar caracteres de control
            line = line.replace(/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/g, '');
            
            // Validar formato de línea SDP
            // Las líneas SDP válidas empiezan con una letra seguida de '='
            if (line.match(/^[a-z]=/i)) {
                // ELIMINAR líneas opcionales problemáticas
                let isProblematic = false;
                for (const problematic of problematicLines) {
                    if (line.startsWith(problematic)) {
                        console.warn(`Línea ${problematic} eliminada (opcional y problemática):`, line);
                        isProblematic = true;
                        break;
                    }
                }
                if (isProblematic) {
                    continue; // Saltar esta línea completamente
                }
                // ELIMINAR todas las líneas a=ssrc y a=ssrc-group - son opcionales y causan muchos problemas
                // Las líneas ssrc son para sincronización de streams pero no son necesarias
                if (line.startsWith('a=ssrc:') || line.startsWith('a=ssrc-group:')) {
                    console.warn('Línea a=ssrc/a=ssrc-group eliminada (opcional y problemática):', line);
                    continue; // Saltar completamente
                }
                
                // Para otras líneas SDP, agregarlas tal cual
                cleanedLines.push(line);
            } else {
                // Si no es una línea SDP válida, intentar limpiarla o saltarla
                // Algunas líneas pueden ser comentarios o metadatos
                if (line.length > 0 && !line.startsWith('#')) {
                    cleanedLines.push(line);
                }
            }
        }
        
        return cleanedLines.join('\r\n');
    }
    
    startSignalingPolling() {
        // Solo iniciar si no hay una conexión activa
        if (this.signalingEventSource || this.signalingInterval) {
            return;
        }
        
        // Intentar usar Server-Sent Events primero (más eficiente)
        if (typeof EventSource !== 'undefined') {
            this.startSignalingSSE();
        } else {
            // Fallback a polling tradicional si SSE no está disponible
            this.startSignalingPollingFallback();
        }
    }
    
    startSignalingSSE() {
        try {
            // Crear conexión SSE (usa cookies de sesión automáticamente)
            const url = `/chats/${this.chatId}/videollamada/stream`;
            this.signalingEventSource = new EventSource(url);
            
            this.signalingEventSource.onopen = () => {
                console.log('SSE conectado para señalización');
            };
            
            this.signalingEventSource.onmessage = async (event) => {
                // Ignorar heartbeats (comentarios)
                if (event.data.startsWith(':')) {
                    return;
                }
                
                try {
                    const data = JSON.parse(event.data);
                    
                    if (data.type === 'timeout') {
                        console.log('SSE timeout, reconectando...');
                        this.stopSignalingPolling();
                        // Reconectar después de un breve delay
                        setTimeout(() => this.startSignalingPolling(), 1000);
                        return;
                    }
                    
                    // Procesar mensaje de señalización
                    if (data.tipo && data.datos) {
                        console.log('Mensaje SSE recibido:', data.tipo);
                        
                        // Si es un offer y no estamos en llamada, mostrar notificación
                        if (data.tipo === 'offer' && !this.isInCall && !this.peerConnection) {
                            console.log('Llamada entrante detectada vía SSE');
                            this.showIncomingCallNotification(data.datos);
                            return; // No procesar el offer todavía, esperar a que el usuario acepte
                        }
                        
                        // Para otros tipos de mensajes, necesitamos peerConnection
                        if (!this.peerConnection) {
                            console.warn('Mensaje recibido pero no hay peerConnection activa');
                            return;
                        }
                        
                        await this.handleSignalingMessage(data.tipo, data.datos);
                    }
                } catch (error) {
                    // Si no es JSON, probablemente es un heartbeat
                    if (error.message && error.message.includes('JSON')) {
                        return;
                    }
                    console.error('Error al procesar mensaje SSE:', error);
                }
            };
            
            this.signalingEventSource.onerror = (error) => {
                console.warn('Error en SSE, cambiando a polling fallback');
                this.stopSignalingPolling();
                // Cambiar a polling tradicional si SSE falla
                setTimeout(() => this.startSignalingPollingFallback(), 1000);
            };
            
            console.log('SSE iniciado para señalización');
        } catch (error) {
            console.warn('No se pudo iniciar SSE, usando polling fallback:', error);
            this.startSignalingPollingFallback();
        }
    }
    
    startSignalingPollingFallback() {
        let pollingAttempts = 0;
        const maxPollingAttempts = 300; // 10 minutos máximo (300 * 2 segundos)
        
        this.signalingInterval = setInterval(async () => {
            // No salir si estamos en llamada, pero sí si tenemos peerConnection activa esperando respuesta
            if (this.isInCall) {
                pollingAttempts = 0; // Resetear contador si ya estamos en llamada
                return;
            }
            
            pollingAttempts++;
            
            // Limitar el tiempo de polling para evitar consumo excesivo
            if (pollingAttempts > maxPollingAttempts) {
                console.warn('Polling de señalización excedió el tiempo máximo');
                this.stopSignalingPolling();
                if (this.estadoVideollamada) {
                    this.estadoVideollamada.innerHTML = `
                        <i class="fa fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                        <h5>Timeout de conexión</h5>
                        <p>La conexión tardó demasiado. Por favor, intenta de nuevo.</p>
                    `;
                }
                return;
            }
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    console.warn('No se encontró token CSRF');
                    return;
                }
                
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000); // Timeout de 5 segundos
                
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
                    console.warn('Error en respuesta de polling:', response.status, response.statusText);
                    return;
                }
                
                const result = await response.json();
                if (result.success && result.mensajes && result.mensajes.length > 0) {
                    for (const mensaje of result.mensajes) {
                        // Si es un offer y no estamos en llamada, mostrar notificación
                        if (mensaje.tipo === 'offer' && !this.isInCall && !this.peerConnection) {
                            console.log('Llamada entrante detectada vía polling');
                            this.showIncomingCallNotification(mensaje.datos);
                            continue; // No procesar el offer todavía, esperar a que el usuario acepte
                        }
                        
                        // Para otros tipos de mensajes, necesitamos peerConnection
                        if (!this.peerConnection) {
                            console.warn('Mensaje recibido pero no hay peerConnection activa');
                            continue;
                        }
                        
                        await this.handleSignalingMessage(mensaje.tipo, mensaje.datos);
                    }
                    pollingAttempts = 0; // Resetear contador al recibir mensajes
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    console.warn('Timeout en polling de señalización');
                } else {
                    console.error('Error en polling de señalización:', error);
                }
            }
        }, 2000);
    }
    
    stopSignalingPolling() {
        // Cerrar SSE si está activo
        if (this.signalingEventSource) {
            this.signalingEventSource.close();
            this.signalingEventSource = null;
        }
        
        // Detener polling tradicional si está activo
        if (this.signalingInterval) {
            clearInterval(this.signalingInterval);
            this.signalingInterval = null;
        }
    }
    
    toggleAudio() {
        if (this.localStream && this.btnToggleAudio) {
            const audioTrack = this.localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                this.isAudioMuted = !audioTrack.enabled;
                
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
    
    toggleVideo() {
        if (this.localStream && this.btnToggleVideo) {
            const videoTrack = this.localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                this.isVideoMuted = !videoTrack.enabled;
                
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
    
    endCall() {
        if (this.isInCall) {
            this.isInCall = false;
            this.stopCallTimer();
        }
        
        if (this.peerConnection) {
            this.peerConnection.close();
            this.peerConnection = null;
        }
        
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }
        
        if (this.remoteStream) {
            this.remoteStream.getTracks().forEach(track => track.stop());
            this.remoteStream = null;
        }
        
        if (this.videoLocal) {
            this.videoLocal.srcObject = null;
        }
        if (this.videoRemoto) {
            this.videoRemoto.srcObject = null;
        }
        
        this.stopSignalingPolling();
        this.stopCallSound();
        this.incomingCallOffer = null;
        
        // Cerrar modal de notificación si está abierto
        if (this.modalLlamadaEntrante) {
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(this.modalLlamadaEntrante);
                if (modal) modal.hide();
            } else {
                this.modalLlamadaEntrante.style.display = 'none';
                this.modalLlamadaEntrante.classList.remove('show');
            }
        }
        
        // Cerrar el modal correctamente
        if (typeof bootstrap !== 'undefined' && this.modalVideollamada) {
            try {
                const modal = bootstrap.Modal.getInstance(this.modalVideollamada);
                if (modal) {
                    modal.hide();
                } else {
                    this.closeModalManually();
                }
            } catch (error) {
                console.error('Error al cerrar modal con Bootstrap:', error);
                this.closeModalManually();
            }
        } else {
            this.closeModalManually();
        }
        
        if (this.btnToggleAudio) {
            this.btnToggleAudio.innerHTML = '<i class="fa fa-microphone"></i>';
            this.btnToggleAudio.classList.remove('btn-danger');
        }
        if (this.btnToggleVideo) {
            this.btnToggleVideo.innerHTML = '<i class="fa fa-video-camera"></i>';
            this.btnToggleVideo.classList.remove('btn-danger');
        }
        
        if (this.estadoVideollamada) {
            this.estadoVideollamada.style.display = 'none';
        }
        this.isInitiator = false;
        this.callId = null;
    }
    
    closeModalManually() {
        if (this.modalVideollamada) {
            this.modalVideollamada.style.display = 'none';
            this.modalVideollamada.classList.remove('show');
            
            // NO remover clases del body que no agregamos
            // document.body.classList.remove('modal-open');
            
            // NO remover backdrops que no creamos
            // const backdrops = document.querySelectorAll('.modal-backdrop');
            // backdrops.forEach(backdrop => {
            //     backdrop.remove();
            // });
            
            // NO modificar estilos del body
            // document.body.style.overflow = '';
            // document.body.style.paddingRight = '';
        }
    }
    
    showModalManually() {
        if (this.modalVideollamada) {
            this.modalVideollamada.style.display = 'block';
            this.modalVideollamada.classList.add('show');
            
            // NO bloquear el scroll del body
            // document.body.classList.add('modal-open');
            
            // NO crear backdrop que oscurezca la pantalla
            // if (!document.querySelector('.modal-backdrop')) {
            //     const backdrop = document.createElement('div');
            //     backdrop.className = 'modal-backdrop fade show';
            //     backdrop.style.zIndex = '1040';
            //     document.body.appendChild(backdrop);
            // }
        }
    }
    
    startCallTimer() {
        this.callStartTime = Date.now();
        this.callTimer = setInterval(() => {
            if (this.tiempoVideollamada) {
                const elapsed = Math.floor((Date.now() - this.callStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                this.tiempoVideollamada.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    }
    
    stopCallTimer() {
        if (this.callTimer) {
            clearInterval(this.callTimer);
            this.callTimer = null;
        }
        if (this.tiempoVideollamada) {
            this.tiempoVideollamada.textContent = '00:00';
        }
    }
}

window.VideoCall = VideoCall;
