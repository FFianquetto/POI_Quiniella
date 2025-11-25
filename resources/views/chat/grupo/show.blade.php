@extends('layouts.app')

@section('template_title')
    {{ __('Chat Grupal') }} - {{ $chat->nombre }}
@endsection

@section('content')
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
                                {{ $chat->nombre }}
                            </span>
                            <small class="text-muted ms-2">{{ $chat->contarMiembros() }} miembros</small>
                    </div>
                    <div>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalInfoGrupo">
                                <i class="fa fa-info-circle"></i> Info
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body" style="height: 400px; overflow-y: auto;" id="chat-messages">
                    @if($chat->mensajes->count() > 0)
                        <div class="chat-messages">
                            @foreach ($chat->mensajes as $mensaje)
                                <div class="message {{ $mensaje->registro_id_emisor == session('registro_id') ? 'message-own' : 'message-other' }} mb-3">
                                    <div class="message-content">
                                        @if($mensaje->registro_id_emisor != session('registro_id'))
                                            <div class="message-sender">
                                                <strong>{{ $mensaje->emisor->nombre }}</strong>
                                            </div>
                    @endif
                    
                                        @if($mensaje->tipo === 'texto')
                                            <div class="message-text">
                                                {{ $mensaje->contenido }}
                                            </div>
                                        @elseif($mensaje->tipo === 'imagen')
                                            <div class="message-media">
                                                @if($mensaje->archivo_url)
                                                    <img src="{{ $mensaje->archivo_url }}" alt="{{ $mensaje->archivo_nombre }}" class="img-fluid rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                                    <small class="d-block mt-1">{{ $mensaje->archivo_nombre }}</small>
                                                @else
                                                    <div class="alert alert-warning">Imagen no disponible</div>
                    @endif
                </div>
                                        @elseif($mensaje->tipo === 'video')
                                            <div class="message-media">
                                                @if($mensaje->archivo_url)
                                                    <video controls class="img-fluid rounded" style="max-width: 300px;">
                                                        <source src="{{ $mensaje->archivo_url }}" type="video/mp4">
                                                        <source src="{{ $mensaje->archivo_url }}" type="video/webm">
                                                        Tu navegador no soporta el elemento video.
                                                    </video>
                                                    <small class="d-block mt-1">{{ $mensaje->archivo_nombre }}</small>
                                                @else
                                                    <div class="alert alert-warning">Video no disponible</div>
                                                @endif
            </div>
                                        @elseif($mensaje->tipo === 'audio')
                                            <div class="message-media">
                                                @if($mensaje->archivo_url)
                                                    <audio controls class="w-100">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/mpeg">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/webm">
                                                        <source src="{{ $mensaje->archivo_url }}" type="audio/ogg">
                                                        Tu navegador no soporta el elemento audio.
                                                    </audio>
                                                    <small class="d-block mt-1">{{ $mensaje->archivo_nombre }}</small>
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
                                            @if($mensaje->registro_id_emisor == session('registro_id'))
                                                @if($mensaje->leido)
                                                    <i class="fa fa-check-double text-primary"></i>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            @endif
                                        </small>
                                    </div>
                                </div>
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
                    <form action="{{ route('chat.mensaje', $chat->id) }}" method="POST" enctype="multipart/form-data" id="chat-form">
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
                                    
                                    <button type="submit" class="btn btn-primary">
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

            @php
                $tareasPendientes = $tareasPendientes ?? collect();
                $tareasCompletadas = $tareasCompletadas ?? collect();
            @endphp

            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-tasks me-2"></i>Tareas del grupo
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info">
                            {{ session('info') }}
                        </div>
                    @endif

                    <form action="{{ route('chat.grupo.tareas.crear', $chat->id) }}" method="POST" class="mb-4" id="form-crear-tarea">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Título</label>
                                <input type="text" name="titulo" class="form-control" placeholder="Nueva tarea" required value="{{ old('titulo') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Asignar a</label>
                                <select name="asignado_a" class="form-select">
                                    <option value="">Sin asignar</option>
                                    @foreach($chat->usuarios as $miembro)
                                        <option value="{{ $miembro->id }}" @selected(old('asignado_a') == $miembro->id)>{{ $miembro->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2" placeholder="Detalles adicionales (opcional)">{{ old('descripcion') }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-plus-circle me-1"></i> Crear tarea
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-uppercase text-muted">Pendientes</h6>
                            @forelse($tareasPendientes as $tarea)
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $tarea->titulo }}</strong>
                                            <div class="text-muted small">
                                                Creada por {{ optional($tarea->creador)->nombre ?? 'N/D' }} · {{ $tarea->created_at?->diffForHumans() }}
                                            </div>
                                            @if($tarea->descripcion)
                                                <p class="mb-2 mt-2">{{ $tarea->descripcion }}</p>
                                            @endif
                                            <div class="small">
                                                @if($tarea->asignadoA)
                                                    <span class="badge bg-info text-dark">Asignada a {{ $tarea->asignadoA->nombre }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Sin asignar</span>
                                                @endif
                                            </div>
                                        </div>
                                        <form action="{{ route('chat.grupo.tareas.completar', [$chat->id, $tarea->id]) }}" method="POST" class="form-completar-tarea">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa fa-check"></i> Completar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">No hay tareas pendientes.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="text-uppercase text-muted">Completadas</h6>
                            @forelse($tareasCompletadas as $tarea)
                                <div class="border rounded p-3 mb-2 bg-light">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong class="text-decoration-line-through">{{ $tarea->titulo }}</strong>
                                            <div class="text-muted small">
                                                Completada por {{ optional($tarea->completadoPor)->nombre ?? 'N/D' }}
                                                @if($tarea->completado_at)
                                                    · {{ $tarea->completado_at->diffForHumans() }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">Aún no hay tareas completadas.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de información del grupo -->
<div class="modal fade" id="modalInfoGrupo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-info-circle me-2"></i>Información del Grupo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="w-20 h-20 bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white text-3xl font-bold mb-3">
                            {{ substr($chat->nombre, 0, 1) }}
                        </div>
                        <h4 class="mb-1">{{ $chat->nombre }}</h4>
                        @if($chat->descripcion)
                            <p class="text-muted">{{ $chat->descripcion }}</p>
                            @endif
                    </div>
                    <div class="col-md-8">
                        <h5>Miembros ({{ $chat->contarMiembros() }})</h5>
                        <div class="row">
                    @foreach($chat->usuarios as $miembro)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="w-10 h-10 bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white font-semibold me-3">
                                {{ substr($miembro->nombre, 0, 1) }}
                            </div>
                                        <div>
                                            <div class="fw-bold">{{ $miembro->nombre }} {{ $miembro->apellido }}</div>
                                    @if($miembro->id == $chat->creador_id)
                                                <small class="text-warning">Creador</small>
                                    @elseif($chat->esAdministrador($miembro->id))
                                                <small class="text-primary">Administrador</small>
                                            @else
                                                <small class="text-muted">Miembro</small>
                                @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                            
                        @if($chat->esAdministrador(session('registro_id')))
                            <hr>
                            <div class="d-flex gap-2">
                                <a href="{{ route('chat.grupo.edit', $chat->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-edit"></i> Editar Grupo
                                </a>
                                @if($chat->creador_id != session('registro_id'))
                                    <form action="{{ route('chat.grupo.abandonar', $chat->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro de que quieres abandonar este grupo?')">
                                            <i class="fa fa-sign-out-alt"></i> Abandonar
                                        </button>
                                    </form>
                                @endif
                                </div>
                            @endif
                        </div>
                </div>
            </div>
        </div>
                </div>
            </div>

<div class="modal fade" id="modalVideo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-video-camera me-2"></i>Grabar Video
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <video id="video-preview" class="w-100" autoplay muted style="border-radius: 0;"></video>
                <canvas id="video-canvas" class="d-none"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btn-grabar-video">
                    <i class="fa fa-play me-1"></i>Grabar
                </button>
                <button type="button" class="btn btn-danger" id="btn-detener-video" style="display: none;">
                    <i class="fa fa-stop me-1"></i>Detener
                </button>
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

.message-sender {
    font-size: 0.8rem;
    margin-bottom: 4px;
    opacity: 0.8;
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
    const btnGrabarVideo = document.getElementById('btn-grabar-video');
    const btnDetenerVideo = document.getElementById('btn-detener-video');
    const archivoInput = document.getElementById('archivo-input');
    const controlesGrabacion = document.getElementById('controles-grabacion');
    const tiempoGrabacion = document.getElementById('tiempo-grabacion');
    const progressGrabacion = document.getElementById('progress-grabacion');
    const chatForm = document.getElementById('chat-form');
    const mensajeTipo = document.getElementById('mensaje-tipo');

    btnAdjuntar.addEventListener('click', function() {
        archivoInput.click();
    });

    archivoInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            
            if (file.size > 10 * 1024 * 1024) {
                alert('El archivo es demasiado grande. Máximo 10MB.');
                this.value = '';
                return;
            }
            
            const extension = file.name.split('.').pop().toLowerCase();
            const allowedTypes = {
                'imagen': ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'video': ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
                'audio': ['mp3', 'wav', 'ogg', 'm4a', 'webm'],
                'archivo': ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar']
            };
            
            let tipoEncontrado = 'archivo';
            for (const [tipo, extensiones] of Object.entries(allowedTypes)) {
                if (extensiones.includes(extension)) {
                    tipoEncontrado = tipo;
                    break;
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
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            
            mediaRecorder.ondataavailable = function(event) {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            };
            
            mediaRecorder.onstop = function() {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const audioFile = new File([audioBlob], 'audio_grabado_' + Date.now() + '.webm', { type: 'audio/webm' });
                
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

    btnVideo.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalVideo'));
        modal.show();
        
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(function(stream) {
                const videoPreview = document.getElementById('video-preview');
                videoPreview.srcObject = stream;
                
                btnGrabarVideo.onclick = function() {
                    mediaRecorder = new MediaRecorder(stream);
                    videoChunks = [];
                    
                    mediaRecorder.ondataavailable = function(event) {
                        if (event.data.size > 0) {
                            videoChunks.push(event.data);
                        }
                    };
                    
                    mediaRecorder.onstop = function() {
                        const videoBlob = new Blob(videoChunks, { type: 'video/webm' });
                        const videoFile = new File([videoBlob], 'video_grabado_' + Date.now() + '.webm', { type: 'video/webm' });
                        
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(videoFile);
                        archivoInput.files = dataTransfer.files;
                        
                        mensajeTipo.value = 'video';
                        modal.hide();
                        
                        const mensajeTexto = document.getElementById('mensaje-texto');
                        mensajeTexto.value = '🎥 Video grabado';
                        
                        btnVideo.classList.add('btn-success');
                        setTimeout(() => btnVideo.classList.remove('btn-success'), 2000);
                    };
                    
                    mediaRecorder.start();
                    btnGrabarVideo.style.display = 'none';
                    btnDetenerVideo.style.display = 'block';
                };
                
                btnDetenerVideo.onclick = function() {
                    if (mediaRecorder && mediaRecorder.state === 'recording') {
                        mediaRecorder.stop();
                        stream.getTracks().forEach(track => track.stop());
                    }
                };
            })
            .catch(function(error) {
                alert('Error al acceder a la cámara: ' + error.message);
                modal.hide();
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
            
            if (!btnVideollamada || !modalVideollamada) {
                return;
            }
            
            if (typeof VideoCall === 'undefined') {
                return;
            }
            
            videoCall = new VideoCall({{ $chat->id }}, {{ session('registro_id') ?? 'null' }});
            
        } catch (error) {
            return;
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
            const script = document.createElement('script');
            const baseUrl = '{{ rtrim(config("app.url", url("/")), "/") }}';
            script.src = baseUrl + '/js/videollamada.js';
            console.log('Cargando videollamada.js desde:', script.src);
            script.onload = function() {
                setTimeout(initializeVideoCall, 100);
            };
            script.onerror = function() {
                return;
            };
            document.head.appendChild(script);
        } else {
            initializeVideoCall();
        }
    }
});

// Interceptar formularios de tareas para recargar la página después de enviar
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de crear tarea
    const formCrearTarea = document.getElementById('form-crear-tarea');
    if (formCrearTarea) {
        formCrearTarea.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Deshabilitar botón y mostrar estado de carga
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Creando...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok || response.redirected) {
                    // Recargar la página después de crear la tarea
                    window.location.reload();
                } else {
                    // Si hay error, recargar de todas formas para mostrar mensajes de error
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error al crear tarea:', error);
                // Recargar la página de todas formas
                window.location.reload();
            });
        });
    }
    
    // Formularios de completar tarea
    const formsCompletarTarea = document.querySelectorAll('form.form-completar-tarea');
    formsCompletarTarea.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Deshabilitar botón y mostrar estado de carga
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok || response.redirected) {
                    // Recargar la página después de completar la tarea
                    window.location.reload();
                } else {
                    // Si hay error, recargar de todas formas
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error al completar tarea:', error);
                // Recargar la página de todas formas
                window.location.reload();
            });
        });
    });
});
</script>
@endsection