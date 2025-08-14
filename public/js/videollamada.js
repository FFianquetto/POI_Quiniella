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
        
        this.configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' }
            ]
        };
        
        this.initializeElements();
        this.bindEvents();
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
        
        if (this.modalVideollamada) {
            this.modalVideollamada.addEventListener('hidden.bs.modal', () => this.endCall());
            this.modalVideollamada.addEventListener('hide.bs.modal', () => this.endCall());
        }
    }
    
    async iniciarVideollamada() {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('WebRTC no está soportado en este navegador');
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
            this.remoteStream = event.streams[0];
            if (this.videoRemoto) {
                this.videoRemoto.srcObject = this.remoteStream;
            }
            if (this.estadoVideollamada) {
                this.estadoVideollamada.style.display = 'none';
            }
            this.isInCall = true;
            this.startCallTimer();
        };
        
        this.peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                this.sendSignalingMessage('ice-candidate', event.candidate);
            }
        };
        
        this.peerConnection.onconnectionstatechange = () => {
            if (!this.estadoVideollamada) return;
            
            switch (this.peerConnection.connectionState) {
                case 'connected':
                    this.estadoVideollamada.style.display = 'none';
                    this.isInCall = true;
                    this.startCallTimer();
                    break;
                case 'disconnected':
                case 'failed':
                    this.endCall();
                    break;
                case 'connecting':
                    this.estadoVideollamada.innerHTML = `
                        <i class="fa fa-spinner fa-spin fa-3x mb-3"></i>
                        <h5>Conectando...</h5>
                        <p>Estableciendo conexión con el otro usuario</p>
                    `;
                    break;
            }
        };
        
        this.peerConnection.oniceconnectionstatechange = () => {
            if (this.peerConnection.iceConnectionState === 'failed') {
                this.endCall();
            }
        };
    }
    
    async sendSignalingMessage(tipo, datos) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) return;
            
            const response = await fetch(`/chats/${this.chatId}/videollamada/señalizacion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tipo: tipo,
                    datos: datos,
                    call_id: this.callId,
                    usuario_id: this.usuarioId
                })
            });
            
            const result = await response.json();
            if (!result.success) {
                return;
            }
        } catch (error) {
            return;
        }
    }
    
    async handleSignalingMessage(tipo, datos) {
        if (!this.peerConnection) return;
        
        try {
            switch (tipo) {
                case 'offer':
                    await this.peerConnection.setRemoteDescription(new RTCSessionDescription(datos));
                    const answer = await this.peerConnection.createAnswer();
                    await this.peerConnection.setLocalDescription(answer);
                    await this.sendSignalingMessage('answer', answer);
                    break;
                    
                case 'answer':
                    await this.peerConnection.setRemoteDescription(new RTCSessionDescription(datos));
                    break;
                    
                case 'ice-candidate':
                    await this.peerConnection.addIceCandidate(new RTCIceCandidate(datos));
                    break;
            }
        } catch (error) {
            return;
        }
    }
    
    startSignalingPolling() {
        this.signalingInterval = setInterval(async () => {
            if (!this.peerConnection || this.isInCall) return;
        }, 2000);
    }
    
    stopSignalingPolling() {
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
