# Funcionalidad de Videollamada

## 🎯 Descripción

Esta funcionalidad permite realizar videollamadas en tiempo real entre usuarios de la aplicación usando WebRTC (Web Real-Time Communication). La implementación es peer-to-peer, lo que significa que la comunicación se establece directamente entre los dispositivos de los usuarios sin pasar por un servidor central.

## 🚀 Características

### ✅ Funcionalidades Implementadas

- **Videollamada en tiempo real** usando WebRTC
- **Conexión peer-to-peer** entre dispositivos
- **Controles de audio y video** (silenciar/activar micrófono, activar/desactivar cámara)
- **Interfaz de usuario intuitiva** con modal dedicado
- **Indicador de tiempo** de la llamada
- **Estados de conexión** visuales
- **Compatibilidad con múltiples navegadores**

### 🎨 Interfaz de Usuario

- **Botón de videollamada** en la barra de herramientas del chat
- **Modal de videollamada** con diseño responsive
- **Video principal** (remoto) en pantalla completa
- **Video local** (pequeño) en la esquina
- **Controles de llamada** (audio, video, colgar)
- **Indicador de estado** de conexión
- **Timer de llamada** en tiempo real

## 🔧 Implementación Técnica

### Arquitectura

```
Usuario A (Iniciador) ←→ WebRTC ←→ Usuario B (Receptor)
     ↓                        ↓
  Cámara/Micrófono      Conexión P2P
     ↓                        ↓
  Video Local           Video Remoto
```

### Componentes Principales

1. **VideoCall.js** - Clase principal que maneja toda la lógica de WebRTC
2. **ChatController.php** - Controlador que maneja la señalización
3. **Vista show.blade.php** - Interfaz de usuario para la videollamada
4. **Rutas web.php** - Endpoints para la funcionalidad

### Tecnologías Utilizadas

- **WebRTC** - Para comunicación peer-to-peer
- **STUN Servers** - Para NAT traversal
- **MediaDevices API** - Para acceso a cámara y micrófono
- **Bootstrap** - Para la interfaz de usuario
- **Laravel** - Para el backend y señalización

## 📁 Estructura de Archivos

```
resources/
├── views/
│   └── chat/
│       └── show.blade.php          # Vista principal con modal de videollamada
├── js/
│   └── videollamada.js            # Clase JavaScript para WebRTC
└── public/
    └── js/
        └── videollamada.js        # Archivo JavaScript compilado

app/
└── Http/
    └── Controllers/
        └── ChatController.php     # Controlador con métodos de videollamada

routes/
└── web.php                        # Rutas para videollamada
```

## 🎮 Cómo Usar

### Para el Usuario

1. **Iniciar videollamada**:
   - Abre un chat con otro usuario
   - Haz clic en el botón 📞 (teléfono) en la barra de herramientas
   - Permite acceso a la cámara y micrófono cuando el navegador lo solicite
   - Espera a que el otro usuario responda

2. **Durante la videollamada**:
   - **Silenciar/Activar micrófono**: Haz clic en el botón 🎤
   - **Activar/Desactivar cámara**: Haz clic en el botón 📹
   - **Colgar llamada**: Haz clic en el botón rojo 📞

3. **Finalizar videollamada**:
   - Haz clic en el botón "Colgar" o cierra el modal
   - La conexión se terminará automáticamente

### Para el Desarrollador

1. **Configuración inicial**:
   ```bash
   # Asegúrate de que el archivo JavaScript esté en la carpeta pública
   cp resources/js/videollamada.js public/js/videollamada.js
   ```

2. **Verificar rutas**:
   ```php
   // En routes/web.php
   Route::post('/chats/{chat}/videollamada/iniciar', [ChatController::class, 'iniciarVideollamada']);
   Route::post('/chats/{chat}/videollamada/señalizacion', [ChatController::class, 'señalizacion']);
   ```

3. **Configurar STUN servers** (opcional):
   ```javascript
   // En videollamada.js
   this.configuration = {
       iceServers: [
           { urls: 'stun:stun.l.google.com:19302' },
           { urls: 'stun:stun1.l.google.com:19302' },
           { urls: 'stun:stun2.l.google.com:19302' }
       ]
   };
   ```

## 🔒 Seguridad y Privacidad

### Permisos Requeridos

- **Cámara**: Para transmitir video
- **Micrófono**: Para transmitir audio
- **HTTPS**: Requerido para WebRTC en producción

### Consideraciones de Seguridad

- **Conexión peer-to-peer**: Los datos no pasan por el servidor
- **Encriptación**: WebRTC incluye encriptación por defecto
- **Permisos explícitos**: El usuario debe autorizar acceso a cámara/micrófono

## 🐛 Solución de Problemas

### Problemas Comunes

1. **Error de permisos de cámara/micrófono**:
   - Verifica que el sitio use HTTPS
   - Asegúrate de que el usuario haya dado permisos
   - Revisa la configuración del navegador

2. **Conexión fallida**:
   - Verifica la conectividad a internet
   - Revisa los logs del navegador para errores ICE
   - Asegúrate de que los STUN servers estén disponibles

3. **Video no se muestra**:
   - Verifica que la cámara no esté en uso por otra aplicación
   - Revisa la configuración de privacidad del navegador
   - Asegúrate de que el dispositivo tenga cámara

### Debugging

```javascript
// Habilitar logs detallados
console.log('Estado de conexión:', peerConnection.connectionState);
console.log('Estado ICE:', peerConnection.iceConnectionState);
console.log('Candidatos ICE:', peerConnection.localDescription);
```

## 🔄 Mejoras Futuras

### Funcionalidades Planificadas

- [ ] **WebSockets** para señalización en tiempo real
- [ ] **Grabación de videollamadas**
- [ ] **Pantalla compartida**
- [ ] **Chat de texto durante la llamada**
- [ ] **Videollamadas grupales**
- [ ] **Filtros de video**
- [ ] **Efectos de audio**

### Optimizaciones Técnicas

- [ ] **TURN servers** para casos de NAT restrictivo
- [ ] **Adaptive bitrate** para diferentes conexiones
- [ ] **Compresión de video** optimizada
- [ ] **Fallback a audio** cuando el video falla

## 📞 Soporte

Para problemas técnicos o preguntas sobre la implementación:

1. Revisa los logs del navegador (F12 → Console)
2. Verifica la conectividad a internet
3. Asegúrate de que el navegador soporte WebRTC
4. Consulta la documentación de WebRTC MDN

## 📄 Licencia

Esta funcionalidad está incluida en el proyecto principal y sigue las mismas condiciones de licencia.
