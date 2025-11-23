@extends('layouts.app')

@section('template_title')
    {{ __('Chat') }} - {{ $otroUsuario ? $otroUsuario->nombre : 'Chat Grupal' }}
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <a href="{{ route('chat.index') }}" class="btn btn-secondary btn-sm me-2">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                            <span class="h5 mb-0">
                                @if($otroUsuario)
                                    Chat con {{ $otroUsuario->nombre }}
                                @else
                                    Chat Grupal
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="height: 400px; overflow-y: auto;" id="chat-messages">
                    @if($mensajes->count() > 0)
                        <div class="chat-messages">
                            @foreach ($mensajes as $mensaje)
                                @if($mensaje->tipo === 'llamada_perdida')
                                    <div class="message-call-lost-container text-center my-2">
                                        <div class="message-call-lost d-inline-block px-3 py-2 rounded">
                                            <i class="fa fa-phone-slash text-danger me-2"></i>
                                            <span class="text-muted fst-italic">{{ $mensaje->contenido }}</span>
                                            <small class="text-muted d-block mt-1">{{ $mensaje->created_at->format('H:i') }}</small>
                                        </div>
                                    </div>
                                @else
                                <div class="message {{ $mensaje->registro_id_emisor == $usuario->id ? 'message-own' : 'message-other' }} mb-3">
                                    <div class="message-content">
                                        @if($mensaje->tipo === 'texto')
                                            <div class="message-text">
                                                {{ $mensaje->contenido }}
                                            </div>
                                        @elseif($mensaje->tipo === 'imagen')
                                            <div class="message-media">
                                                @if($mensaje->archivo_url)
                                                    <img src="{{ $mensaje->archivo_url }}" 
                                                         alt="{{ $mensaje->archivo_nombre }}" 
                                                         class="img-fluid rounded" 
                                                         style="max-width: 200px; max-height: 200px; object-fit: cover; cursor: pointer;"
                                                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';"
                                                         onclick="window.open('{{ $mensaje->archivo_url }}', '_blank');">
                                                    <small class="d-block mt-1">{{ $mensaje->archivo_nombre }}</small>
                                                @else
                                                    <div class="alert alert-warning">Imagen no disponible</div>
                                                @endif
                                            </div>
                                        @elseif($mensaje->tipo === 'video')
                                            <div class="message-media">
                                                @php
                                                    // Debug: verificar qué valores tenemos
                                                    $tieneUrl = !empty($mensaje->archivo_url);
                                                    $tieneNombre = !empty($mensaje->archivo_nombre);
                                                @endphp
                                                @if($tieneUrl)
                                                    @php
                                                        $extension = strtolower(pathinfo($mensaje->archivo_nombre ?? 'video.webm', PATHINFO_EXTENSION));
                                                        $mimeTypes = [
                                                            'mp4' => 'video/mp4',
                                                            'webm' => 'video/webm;codecs=vp8,opus',
                                                            'ogg' => 'video/ogg',
                                                            'avi' => 'video/x-msvideo',
                                                            'mov' => 'video/quicktime',
                                                            'wmv' => 'video/x-ms-wmv',
                                                            'flv' => 'video/x-flv'
                                                        ];
                                                        // Para webm, siempre usar video/webm
                                                        if ($extension === 'webm' || empty($extension)) {
                                                            $mimeType = 'video/webm;codecs=vp8,opus';
                                                        } else {
                                                            $mimeType = $mimeTypes[$extension] ?? 'video/webm;codecs=vp8,opus';
                                                        }
                                                    @endphp
                                                    <video controls class="img-fluid rounded video-player" style="max-width: 300px; max-height: 400px; object-fit: contain; display: block;" preload="metadata" playsinline crossorigin="anonymous" 
                                                        onerror="console.error('Error al cargar video:', this.error, 'URL:', '{{ $mensaje->archivo_url }}'); 
                                                        const errorDiv = document.createElement('div');
                                                        errorDiv.className = 'alert alert-warning';
                                                        errorDiv.innerHTML = 'Error al cargar el video. <a href=\'{{ $mensaje->archivo_url }}\' target=\'_blank\'>Intentar descargar</a>';
                                                        this.parentElement.replaceChild(errorDiv, this);"
                                                        onloadeddata="console.log('Video cargado exitosamente:', '{{ $mensaje->archivo_url }}');"
                                                        onloadstart="console.log('Iniciando carga de video:', '{{ $mensaje->archivo_url }}');">
                                                        @if($extension === 'webm' || empty($extension))
                                                            <source src="{{ $mensaje->archivo_url }}" type="video/webm;codecs=vp8,opus">
                                                            <source src="{{ $mensaje->archivo_url }}" type="video/webm;codecs=vp9,opus">
                                                            <source src="{{ $mensaje->archivo_url }}" type="video/webm">
                                                        @else
                                                            <source src="{{ $mensaje->archivo_url }}" type="{{ $mimeType }}">
                                                        @endif
                                                        <source src="{{ $mensaje->archivo_url }}" type="video/webm">
                                                        <source src="{{ $mensaje->archivo_url }}" type="video/webm;codecs=vp8,opus">
                                                        <source src="{{ $mensaje->archivo_url }}" type="video/webm;codecs=vp9,opus">
                                                        <source src="{{ $mensaje->archivo_url }}" type="video/mp4">
                                                        <source src="{{ $mensaje->archivo_url }}" type="video/ogg">
                                                        Tu navegador no soporta el elemento video.
                                                    </video>
                                                    <small class="d-block mt-1 text-muted" style="font-size: 0.75rem;">
                                                        {{ $mensaje->archivo_nombre ?? 'Video grabado' }}
                                                        @if(config('app.debug'))
                                                            <br><small style="font-size: 0.6rem; color: #999;">URL: {{ $mensaje->archivo_url }}</small>
                                                        @endif
                                                    </small>
                                                @else
                                                    <div class="alert alert-warning">
                                                        Video no disponible
                                                        @if(config('app.debug'))
                                                            <br><small>Debug: URL={{ $mensaje->archivo_url ?? 'null' }}, Nombre={{ $mensaje->archivo_nombre ?? 'null' }}, Tipo={{ $mensaje->tipo }}</small>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($mensaje->tipo === 'audio')
                                            <div class="message-media">
                                                @if($mensaje->archivo_url)
                                                    @php
                                                        $extension = strtolower(pathinfo($mensaje->archivo_nombre, PATHINFO_EXTENSION));
                                                        $mimeTypes = [
                                                            'mp3' => 'audio/mpeg',
                                                            'wav' => 'audio/wav',
                                                            'ogg' => 'audio/ogg',
                                                            'webm' => 'audio/webm;codecs=opus', // Especificar codec para webm
                                                            'm4a' => 'audio/mp4',
                                                            'mp4' => 'audio/mp4', // MP4 también puede ser audio (M4A)
                                                            'aac' => 'audio/aac'
                                                        ];
                                                        // Para webm, verificar si el nombre del archivo indica que es audio
                                                        if ($extension === 'webm') {
                                                            // Si el nombre contiene "audio", es audio webm
                                                            if (stripos($mensaje->archivo_nombre, 'audio') !== false) {
                                                                $mimeType = 'audio/webm;codecs=opus';
                                                            } else {
                                                                // Por defecto, intentar como audio webm
                                                                $mimeType = 'audio/webm;codecs=opus';
                                                            }
                                                        } else {
                                                            $mimeType = $mimeTypes[$extension] ?? 'audio/mpeg';
                                                        }
                                                    @endphp
                                                    <audio controls class="w-100 audio-player" preload="metadata" style="outline: none; width: 100%; min-width: 250px;" crossorigin="anonymous">
                                                        <source src="{{ $mensaje->archivo_url }}" type="{{ $mimeType }}">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/webm">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/webm;codecs=opus">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/mpeg">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/ogg">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/wav">
                                                        Tu navegador no soporta el elemento audio.
                                                    </audio>
                                                    <small class="d-block mt-1 text-muted" style="font-size: 0.75rem;">{{ $mensaje->archivo_nombre }}</small>
                                                @else
                                                    <div class="alert alert-warning">Audio no disponible</div>
                                                @endif
                                            </div>
                                        @elseif($mensaje->tipo === 'archivo')
                                            <div class="message-file">
                                                @if($mensaje->archivo_url)
                                                    <i class="fa fa-file"></i>
                                                    <a href="{{ $mensaje->archivo_url }}" target="_blank" class="text-decoration-none">
                                                        {{ $mensaje->archivo_nombre }}
                                                    </a>
                                                @else
                                                    <div class="alert alert-warning">Archivo no disponible</div>
                                                @endif
                                            </div>
                                        @endif
                                        <small class="message-time text-muted">
                                            {{ $mensaje->created_at->format('H:i') }}
                                            @if($mensaje->registro_id_emisor == $usuario->id)
                                                @if($mensaje->leido)
                                                    <i class="fa fa-check-double text-primary"></i>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-comments fa-3x text-muted mb-3"></i>
                            <h5>No hay mensajes aún</h5>
                            <p class="text-muted">Comienza la conversación enviando un mensaje</p>
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <form action="{{ route('chat.mensaje', ['chat' => $chat->id]) }}" method="POST" enctype="multipart/form-data" id="chat-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input type="text" name="contenido" class="form-control" placeholder="Escribe tu mensaje..." id="mensaje-texto">
                                    <input type="hidden" name="tipo" value="texto" id="mensaje-tipo">
                                    <input type="file" name="archivo" id="archivo-input" class="d-none" accept="image/*,video/*,audio/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.txt,.zip,.rar">
                                    
                                    <button type="button" class="btn btn-outline-secondary" id="btn-adjuntar" title="Adjuntar archivo">
                                        <i class="fa fa-paperclip"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-info" id="btn-audio" title="Grabar audio">
                                        <i class="fa fa-microphone"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-warning" id="btn-video" title="Grabar video">
                                        <i class="fa fa-video-camera"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-success" id="btn-videollamada" title="Iniciar videollamada">
                                        <i class="fa fa-phone"></i>
                                    </button>
                                    
                                    <button type="submit" class="btn btn-primary" id="btn-enviar-mensaje">
                                        <i class="fa fa-paper-plane"></i> Enviar
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-2" id="controles-grabacion" style="display: none;">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-danger btn-sm me-2" id="btn-detener-grabacion">
                                        <i class="fa fa-stop"></i> Detener
                                    </button>
                                    <span id="tiempo-grabacion" class="text-muted">00:00</span>
                                    <div class="progress flex-grow-1 mx-2" style="height: 5px;">
                                        <div class="progress-bar" id="progress-grabacion" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVideo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fa fa-video-camera me-2"></i>Grabando Video
                    <span class="badge bg-light text-danger ms-2" id="tiempo-video-grabacion">00:00</span>
                </h5>
            </div>
            <div class="modal-body p-0">
                <video id="video-preview" class="w-100" autoplay muted style="border-radius: 0;"></video>
                <canvas id="video-canvas" class="d-none"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btn-cancelar-video" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger btn-lg" id="btn-detener-video">
                    <i class="fa fa-stop me-1"></i>Detener y Enviar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Llamada Entrante -->
<div class="modal fade" id="modalLlamadaEntrante" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="fa fa-phone fa-4x text-primary mb-3" style="animation: pulse 1.5s infinite;"></i>
                    <h4 id="nombre-llamante">Llamada entrante...</h4>
                    <p class="text-muted">¿Deseas aceptar esta videollamada?</p>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-success btn-lg rounded-circle" id="btn-aceptar-llamada" style="width: 60px; height: 60px;">
                        <i class="fa fa-phone"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-lg rounded-circle" id="btn-rechazar-llamada" style="width: 60px; height: 60px;">
                        <i class="fa fa-phone-slash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.message-call-lost-container {
    width: 100%;
}

.message-call-lost {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
    font-size: 0.9rem;
}
</style>

<div class="modal fade" id="modalVideollamada" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fa fa-phone me-2"></i>Videollamada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="btn-cerrar-videollamada"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-8">
                        <div class="position-relative" style="height: 400px; background: #000;">
                            <video id="video-remoto" class="w-100 h-100" autoplay playsinline style="object-fit: cover;"></video>
                            <div id="estado-videollamada" class="position-absolute top-50 start-50 translate-middle text-white text-center" style="display: none;">
                                <i class="fa fa-spinner fa-spin fa-3x mb-3"></i>
                                <h5>Conectando...</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative" style="height: 200px; background: #000;">
                            <video id="video-local" class="w-100 h-100" autoplay muted playsinline style="object-fit: cover;"></video>
                        </div>
                        <div class="p-3">
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <button type="button" class="btn btn-outline-light" id="btn-toggle-audio" title="Silenciar/Activar micrófono">
                                    <i class="fa fa-microphone"></i>
                                </button>
                                <button type="button" class="btn btn-outline-light" id="btn-toggle-video" title="Activar/Desactivar cámara">
                                    <i class="fa fa-video-camera"></i>
                                </button>
                                <button type="button" class="btn btn-danger" id="btn-colgar" title="Colgar">
                                    <i class="fa fa-phone"></i>
                                </button>
                            </div>
                            <div class="text-center">
                                <small class="text-muted" id="tiempo-videollamada">00:00</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-messages {
    padding: 15px;
    background-color: #f8f9fa;
}

.message {
    display: flex;
    margin-bottom: 15px;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-own {
    justify-content: flex-end;
}

.message-other {
    justify-content: flex-start;
}

.message-content {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    position: relative;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    word-wrap: break-word;
}

.message-own .message-content {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-other .message-content {
    background: white;
    color: #333;
    border: 1px solid #e9ecef;
    border-bottom-left-radius: 4px;
}

.message-text {
    line-height: 1.4;
    margin-bottom: 5px;
}

.message-time {
    font-size: 0.7rem;
    margin-top: 8px;
    display: block;
    opacity: 0.8;
}

.message-own .message-time {
    color: rgba(255,255,255,0.8);
}

.message-other .message-time {
    color: #6c757d;
}

.message-file {
    padding: 12px;
    background-color: rgba(255,255,255,0.15);
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1px dashed rgba(255,255,255,0.3);
}

.message-other .message-file {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.message-file a {
    color: inherit;
    text-decoration: none;
    font-weight: 500;
}

.message-file a:hover {
    text-decoration: underline;
}

.message-media {
    margin-bottom: 8px;
}

.message-media img,
.message-media video {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message-media audio {
    border-radius: 20px;
    background: rgba(255,255,255,0.1);
    width: 100%;
    height: 40px;
    outline: none;
}

.message-media .audio-player {
    width: 100%;
    min-width: 250px;
    height: 40px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.1);
    outline: none;
}

.message-own .message-media .audio-player {
    background: rgba(255, 255, 255, 0.2);
}

.message-other .message-media .audio-player {
    background: rgba(0, 0, 0, 0.05);
}

.message-media .video-player {
    border-radius: 8px;
    background: #000;
    outline: none;
    display: block;
}

.message-media video::-webkit-media-controls-panel {
    background-color: rgba(0, 0, 0, 0.5);
}

.message-media video::-webkit-media-controls-play-button {
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
}

#controles-grabacion {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin-top: 10px;
}

/* Estilos para los botones */
.input-group {
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.input-group .form-control {
    border: none;
    padding: 12px 20px;
    font-size: 14px;
}

.input-group .form-control:focus {
    box-shadow: none;
    border-color: #007bff;
}

.input-group .btn {
    border: none;
    padding: 12px 16px;
    margin-left: 2px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.input-group .btn:first-child {
    border-top-left-radius: 25px;
    border-bottom-left-radius: 25px;
}

.input-group .btn:last-child {
    border-top-right-radius: 25px;
    border-bottom-right-radius: 25px;
}

/* Iconos más grandes y mejor espaciados */
.btn i {
    font-size: 16px;
    transition: transform 0.2s ease;
}

.btn:hover i {
    transform: scale(1.1);
}

/* Hover effects */
.btn-outline-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
    color: white;
    transform: translateY(-1px);
}

.btn-outline-info {
    background-color: #0dcaf0;
    color: white;
    border-color: #0dcaf0;
}

.btn-outline-info:hover {
    background-color: #0aa2c0;
    border-color: #0aa2c0;
    color: white;
    transform: translateY(-1px);
}

.btn-outline-warning {
    background-color: #ffc107;
    color: black;
    border-color: #ffc107;
}

.btn-outline-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: black;
    transform: translateY(-1px);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-color: #007bff;
    font-weight: 500;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    border-color: #0056b3;
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .message-content {
        max-width: 85%;
    }
    
    .input-group {
        flex-wrap: nowrap;
    }
    
    .btn {
        padding: 10px 12px;
        font-size: 14px;
    }
    
    .btn i {
        font-size: 14px;
    }
    
    .chat-messages {
        padding: 10px;
    }
}

/* Animaciones adicionales */
.btn:active {
    transform: translateY(0);
}

.message-content:hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease;
}

/* Scrollbar personalizado */
#chat-messages::-webkit-scrollbar {
    width: 6px;
}

#chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#chat-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#chat-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<script>
let mediaRecorder;
let audioChunks = [];
let videoChunks = [];
let startTime;
let recordingInterval;

document.addEventListener('DOMContentLoaded', function() {
    const btnAdjuntar = document.getElementById('btn-adjuntar');
    const btnAudio = document.getElementById('btn-audio');
    const btnVideo = document.getElementById('btn-video');
    const btnDetenerGrabacion = document.getElementById('btn-detener-grabacion');
    const btnDetenerVideo = document.getElementById('btn-detener-video');
    const archivoInput = document.getElementById('archivo-input');
    const controlesGrabacion = document.getElementById('controles-grabacion');
    const tiempoGrabacion = document.getElementById('tiempo-grabacion');
    const progressGrabacion = document.getElementById('progress-grabacion');
    const chatForm = document.getElementById('chat-form');
    const mensajeTipo = document.getElementById('mensaje-tipo');
    
    // Validar y enviar el formulario correctamente
    chatForm.addEventListener('submit', function(e) {
        // Detener cualquier conexión SSE de videollamada que pueda estar activa
        // para evitar interferencias con el envío del formulario
        if (typeof videoCall !== 'undefined' && videoCall) {
            try {
                videoCall.stopSignalingPolling();
            } catch (err) {
                console.warn('Error al detener polling de videollamada:', err);
            }
        }
        
        // Obtener elementos del formulario
        const mensajeTexto = document.getElementById('mensaje-texto');
        const archivoInput = document.getElementById('archivo-input');
        const formAction = chatForm.getAttribute('action');
        
        // Verificar que el tenga la acción correcta
        // Debe ser algo como /chats/7/mensaje, NO /chats/7/videollamada/señalizacion
        if (formAction && formAction.includes('videollamada')) {
            console.error('ERROR: El formulario tiene una acción incorrecta:', formAction);
            e.preventDefault();
            alert('Error: El formulario tiene una configuración incorrecta. Por favor, recarga la página.');
            return false;
        }
        
        console.log('Formulario enviado a:', formAction);
        
        // Validar que haya contenido o archivo
        if (!mensajeTexto.value.trim() && (!archivoInput.files || archivoInput.files.length === 0)) {
            e.preventDefault();
            alert('Por favor, escribe un mensaje o adjunta un archivo.');
            return false;
        }
        
        // Si hay archivo, asegurar que el tipo esté correcto
        if (archivoInput.files && archivoInput.files.length > 0) {
            const file = archivoInput.files[0];
            if (file.size > 50 * 1024 * 1024) {
                e.preventDefault();
                alert('El archivo es demasiado grande. Máximo 50MB.');
                return false;
            }
        }
        
        // Verificar que la acción sea correcta ANTES de permitir el envío
        // Debe ser /chats/{chat}/mensaje, NO /chats/{chat}/videollamada/señalizacion
        const actionCorrecta = formAction && formAction.includes('/mensaje') && !formAction.includes('videollamada');
        
        if (!actionCorrecta) {
            e.preventDefault();
            console.error('ERROR: El formulario tiene una acción incorrecta:', formAction);
            console.error('Acción esperada: /chats/{chat}/mensaje');
            alert('Error: El formulario está configurado incorrectamente. Por favor, recarga la página.');
            return false;
        }
        
        // Si todo está bien, permitir que el formulario se envíe normalmente
        console.log('Formulario correcto. Enviando a:', formAction);
    });

    btnAdjuntar.addEventListener('click', function() {
        archivoInput.click();
    });

    archivoInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            
            if (file.size > 50 * 1024 * 1024) {
                alert('El archivo es demasiado grande. Máximo 50MB.');
                this.value = '';
                return;
            }
            
            const extension = file.name.split('.').pop().toLowerCase();
            const allowedTypes = {
                'imagen': ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'video': ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
                'audio': ['mp3', 'wav', 'ogg', 'm4a', 'webm', 'mp4'], // MP4 puede ser audio (M4A)
                'archivo': ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar']
            };
            
            let tipoEncontrado = 'archivo';
            // Para MP4, verificar el tipo MIME del archivo para distinguir entre audio y video
            if (extension === 'mp4') {
                // Verificar el tipo MIME del archivo
                if (file.type && file.type.startsWith('audio/')) {
                    tipoEncontrado = 'audio';
                } else if (file.type && file.type.startsWith('video/')) {
                    tipoEncontrado = 'video';
                } else {
                    // Por defecto, si el nombre contiene "audio" o "video", usar eso
                    const fileName = file.name.toLowerCase();
                    if (fileName.includes('audio') || fileName.includes('grabado') && !fileName.includes('video')) {
                        tipoEncontrado = 'audio';
                    } else {
                        tipoEncontrado = 'video';
                    }
                }
            } else {
                for (const [tipo, extensiones] of Object.entries(allowedTypes)) {
                    if (extensiones.includes(extension)) {
                        tipoEncontrado = tipo;
                        break;
                    }
                }
            }
            
            mensajeTipo.value = tipoEncontrado;
            
            const mensajeTexto = document.getElementById('mensaje-texto');
            mensajeTexto.value = `📎 ${file.name}`;
        }
    });

    btnAudio.addEventListener('click', async function() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            
            // Priorizar MP4/M4A (audio/mp4) sobre webm para mejor compatibilidad
            let mimeType = 'audio/mp4';
            let extension = 'm4a';
            const options = {};
            
            // Verificar qué tipos MIME soporta MediaRecorder, priorizando MP4
            if (MediaRecorder.isTypeSupported('audio/mp4')) {
                options.mimeType = 'audio/mp4';
                mimeType = 'audio/mp4';
                extension = 'm4a';
            } else if (MediaRecorder.isTypeSupported('audio/mpeg')) {
                // Algunos navegadores soportan audio/mpeg directamente
                options.mimeType = 'audio/mpeg';
                mimeType = 'audio/mpeg';
                extension = 'mp3';
            } else if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
                options.mimeType = 'audio/webm;codecs=opus';
                mimeType = 'audio/webm';
                extension = 'webm';
            } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                options.mimeType = 'audio/webm';
                mimeType = 'audio/webm';
                extension = 'webm';
            } else if (MediaRecorder.isTypeSupported('audio/ogg;codecs=opus')) {
                options.mimeType = 'audio/ogg;codecs=opus';
                mimeType = 'audio/ogg';
                extension = 'ogg';
            } else {
                // Fallback por defecto
                options.mimeType = 'audio/webm';
                mimeType = 'audio/webm';
                extension = 'webm';
            }
            
            mediaRecorder = new MediaRecorder(stream, options);
            audioChunks = [];
            
            mediaRecorder.ondataavailable = function(event) {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            };
            
            mediaRecorder.onstop = function() {
                // Usar el tipo MIME detectado
                const audioBlob = new Blob(audioChunks, { type: mimeType });
                const audioFile = new File([audioBlob], 'audio_grabado_' + Date.now() + '.' + extension, { type: mimeType });
                
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(audioFile);
                archivoInput.files = dataTransfer.files;
                
                mensajeTipo.value = 'audio';
                controlesGrabacion.style.display = 'none';
                clearInterval(recordingInterval);
                
                const mensajeTexto = document.getElementById('mensaje-texto');
                mensajeTexto.value = '🎤 Audio grabado';
                
                btnAudio.classList.add('btn-success');
                setTimeout(() => btnAudio.classList.remove('btn-success'), 2000);
            };
            
            mediaRecorder.start();
            startTime = Date.now();
            controlesGrabacion.style.display = 'block';
            btnAudio.disabled = true;
            btnAudio.innerHTML = '<i class="fa fa-microphone-slash"></i>';
            
            recordingInterval = setInterval(function() {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                tiempoGrabacion.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                const progress = Math.min((elapsed / 300) * 100, 100);
                progressGrabacion.style.width = progress + '%';
                
                if (elapsed >= 300) {
                    btnDetenerGrabacion.click();
                }
            }, 1000);
            
        } catch (error) {
            alert('Error al acceder al micrófono: ' + error.message);
        }
    });

    btnDetenerGrabacion.addEventListener('click', function() {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            btnAudio.disabled = false;
            btnAudio.innerHTML = '<i class="fa fa-microphone"></i>';
        }
    });

    let videoStream = null;
    let videoRecordingInterval = null;
    let videoStartTime = null;
    const tiempoVideoGrabacion = document.getElementById('tiempo-video-grabacion');
    const btnCancelarVideo = document.getElementById('btn-cancelar-video');
    
    btnVideo.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalVideo'));
        modal.show();
        
        // Iniciar acceso a la cámara inmediatamente
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(function(stream) {
                videoStream = stream;
                const videoPreview = document.getElementById('video-preview');
                videoPreview.srcObject = stream;
                
                // Iniciar grabación automáticamente
                // FORZAR WebM siempre - es el único formato que funciona confiablemente en móviles
                // MediaRecorder en móviles NO soporta MP4 correctamente, aunque lo reporte como soportado
                let mimeType = 'video/webm';
                let extension = 'webm';
                const options = {};
                
                // Siempre usar WebM - es el formato más compatible en todos los dispositivos
                if (MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus')) {
                    options.mimeType = 'video/webm;codecs=vp9,opus';
                    mimeType = 'video/webm';
                    extension = 'webm';
                    console.log('Usando video/webm;codecs=vp9,opus');
                } else if (MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')) {
                    options.mimeType = 'video/webm;codecs=vp8,opus';
                    mimeType = 'video/webm';
                    extension = 'webm';
                    console.log('Usando video/webm;codecs=vp8,opus');
                } else if (MediaRecorder.isTypeSupported('video/webm')) {
                    options.mimeType = 'video/webm';
                    mimeType = 'video/webm';
                    extension = 'webm';
                    console.log('Usando video/webm');
                } else {
                    // Si WebM no está disponible, el navegador no soporta grabación de video
                    console.error('WebM no está soportado - no se puede grabar video');
                    alert('Tu navegador no soporta la grabación de video. Por favor, usa un navegador moderno.');
                    modal.hide();
                    if (videoStream) {
                        videoStream.getTracks().forEach(track => track.stop());
                        videoStream = null;
                    }
                    return;
                }
                
                console.log('Configuración de grabación (FORZADO WebM):', {
                    mimeType: options.mimeType,
                    extension: extension,
                    soportado: MediaRecorder.isTypeSupported(options.mimeType),
                    userAgent: navigator.userAgent
                });
                
                mediaRecorder = new MediaRecorder(stream, options);
                videoChunks = [];
                
                mediaRecorder.ondataavailable = function(event) {
                    if (event.data && event.data.size > 0) {
                        videoChunks.push(event.data);
                        console.log('Chunk recibido:', event.data.size, 'bytes. Total chunks:', videoChunks.length);
                    }
                };
                
                mediaRecorder.onerror = function(event) {
                    console.error('Error en MediaRecorder:', event.error);
                    alert('Error al grabar el video: ' + (event.error ? event.error.message : 'Error desconocido'));
                    modal.hide();
                    if (videoStream) {
                        videoStream.getTracks().forEach(track => track.stop());
                        videoStream = null;
                    }
                };
                
                mediaRecorder.onstop = function() {
                    // Detener el temporizador
                    if (videoRecordingInterval) {
                        clearInterval(videoRecordingInterval);
                        videoRecordingInterval = null;
                    }
                    
                    // Esperar un momento para asegurar que todos los chunks estén disponibles
                    setTimeout(function() {
                        // Verificar que hay chunks de video
                        if (videoChunks.length === 0) {
                            console.error('No hay chunks de video disponibles');
                            alert('Error: No se pudo grabar el video. Por favor, intenta de nuevo.');
                            modal.hide();
                            if (videoStream) {
                                videoStream.getTracks().forEach(track => track.stop());
                                videoStream = null;
                            }
                            return;
                        }
                        
                        // Usar el tipo MIME detectado
                        const videoBlob = new Blob(videoChunks, { type: mimeType });
                        
                        // Verificar que el blob tiene contenido
                        if (videoBlob.size === 0) {
                            console.error('El blob de video está vacío');
                            alert('Error: El video grabado está vacío. Por favor, intenta de nuevo.');
                            modal.hide();
                            if (videoStream) {
                                videoStream.getTracks().forEach(track => track.stop());
                                videoStream = null;
                            }
                            return;
                        }
                        
                        console.log('Video grabado:', {
                            size: videoBlob.size,
                            type: mimeType,
                            chunks: videoChunks.length
                        });
                        
                        const videoFile = new File([videoBlob], 'video_grabado_' + Date.now() + '.' + extension, { type: mimeType });
                        
                        // Verificar que el archivo se creó correctamente
                        if (!videoFile || videoFile.size === 0) {
                            console.error('Error al crear el archivo de video');
                            alert('Error: No se pudo crear el archivo de video. Por favor, intenta de nuevo.');
                            modal.hide();
                            if (videoStream) {
                                videoStream.getTracks().forEach(track => track.stop());
                                videoStream = null;
                            }
                            return;
                        }
                        
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(videoFile);
                        archivoInput.files = dataTransfer.files;
                        
                        // Verificar que el archivo se agregó al input
                        if (!archivoInput.files || archivoInput.files.length === 0) {
                            console.error('Error: El archivo no se agregó al input');
                            alert('Error: No se pudo agregar el video al formulario. Por favor, intenta de nuevo.');
                            modal.hide();
                            if (videoStream) {
                                videoStream.getTracks().forEach(track => track.stop());
                                videoStream = null;
                            }
                            return;
                        }
                        
                        console.log('Archivo agregado al input:', {
                            name: archivoInput.files[0].name,
                            size: archivoInput.files[0].size,
                            type: archivoInput.files[0].type
                        });
                        
                        mensajeTipo.value = 'video';
                        
                        const mensajeTexto = document.getElementById('mensaje-texto');
                        mensajeTexto.value = '🎥 Video grabado';
                        
                        // Cerrar modal
                        modal.hide();
                        
                        // Detener el stream
                        if (videoStream) {
                            videoStream.getTracks().forEach(track => track.stop());
                            videoStream = null;
                        }
                        
                        // Enviar el formulario automáticamente después de un pequeño delay
                        setTimeout(() => {
                            console.log('Enviando formulario con video...');
                            chatForm.submit();
                        }, 500);
                    }, 100); // Pequeño delay para asegurar que todos los chunks estén listos
                };
                
                // Iniciar grabación automáticamente
                mediaRecorder.start();
                videoStartTime = Date.now();
                
                // Iniciar temporizador
                tiempoVideoGrabacion.textContent = '00:00';
                videoRecordingInterval = setInterval(function() {
                    const elapsed = Math.floor((Date.now() - videoStartTime) / 1000);
                    const minutes = Math.floor(elapsed / 60);
                    const seconds = elapsed % 60;
                    tiempoVideoGrabacion.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }, 1000);
            })
            .catch(function(error) {
                alert('Error al acceder a la cámara: ' + error.message);
                modal.hide();
            });
        
        // Botón de detener y enviar
        btnDetenerVideo.onclick = function() {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                console.log('Deteniendo grabación de video...');
                // Solicitar todos los datos disponibles antes de detener
                mediaRecorder.requestData();
                // Esperar un momento y luego detener
                setTimeout(() => {
                    mediaRecorder.stop();
                }, 100);
            }
        };
        
        // Botón de cancelar
        btnCancelarVideo.onclick = function() {
            // Detener grabación si está activa
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
            }
            
            // Detener stream
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            // Limpiar temporizador
            if (videoRecordingInterval) {
                clearInterval(videoRecordingInterval);
                videoRecordingInterval = null;
            }
            
            // Limpiar chunks
            videoChunks = [];
            
            modal.hide();
        };
        
        // Limpiar cuando se cierra el modal
        document.getElementById('modalVideo').addEventListener('hidden.bs.modal', function() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            if (videoRecordingInterval) {
                clearInterval(videoRecordingInterval);
                videoRecordingInterval = null;
            }
            videoChunks = [];
        });
    });

    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    const observer = new MutationObserver(function() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });
    
    observer.observe(chatMessages, {
        childList: true,
        subtree: true
    });
    
    const mensajeTexto = document.getElementById('mensaje-texto');
    mensajeTexto.addEventListener('input', function() {
        if (this.value.length > 0) {
            this.style.borderColor = '#007bff';
        } else {
            this.style.borderColor = '';
        }
    });

    let videoCall;
    
    function initializeVideoCall() {
        try {
            const btnVideollamada = document.getElementById('btn-videollamada');
            const modalVideollamada = document.getElementById('modalVideollamada');
            const modalLlamadaEntrante = document.getElementById('modalLlamadaEntrante');
            
            console.log('Inicializando videollamada...', {
                btnVideollamada: !!btnVideollamada,
                modalVideollamada: !!modalVideollamada,
                modalLlamadaEntrante: !!modalLlamadaEntrante,
                VideoCall: typeof VideoCall
            });
            
            if (!btnVideollamada) {
                console.error('Botón de videollamada no encontrado');
                return;
            }
            
            if (!modalVideollamada) {
                console.error('Modal de videollamada no encontrado');
                return;
            }
            
            if (typeof VideoCall === 'undefined') {
                console.error('Clase VideoCall no está definida');
                return;
            }
            
            const chatId = {{ $chat->id }};
            const usuarioId = {{ $usuario->id ?? 'null' }};
            
            console.log('Creando instancia de VideoCall:', { chatId, usuarioId });
            
            videoCall = new VideoCall(chatId, usuarioId);
            
            console.log('VideoCall inicializado correctamente');
            
        } catch (error) {
            console.error('Error al inicializar VideoCall:', error);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            loadVideoCallScript();
        });
    } else {
        loadVideoCallScript();
    }
    
    function loadVideoCallScript() {
        if (typeof VideoCall === 'undefined') {
            console.log('Cargando script de videollamada...');
            const script = document.createElement('script');
            script.src = '/js/videollamada.js';
            script.onload = function() {
                console.log('Script de videollamada cargado');
                setTimeout(initializeVideoCall, 100);
            };
            script.onerror = function() {
                console.error('Error al cargar el script de videollamada');
            };
            document.head.appendChild(script);
        } else {
            console.log('VideoCall ya está disponible');
            initializeVideoCall();
        }
    }
});
</script>
@endsection
