<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Chat
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar las opciones para el sistema de chat
    |
    */

    // Tipos de archivos permitidos
    'allowed_file_types' => [
        'imagen' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a', 'webm', 'mp4'], // MP4 también puede ser audio (M4A)
        'archivo' => ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar']
    ],

    // Tamaño máximo de archivo (en bytes)
    'max_file_size' => 50 * 1024 * 1024, // 50MB (aumentado para videos)

    // Duración máxima de grabación (en segundos)
    'max_recording_duration' => 300, // 5 minutos

    // Configuración de almacenamiento
    'storage' => [
        'disk' => 'public',
        'path' => 'chat_archivos'
    ],

    // Configuración de mensajes
    'messages' => [
        'max_length' => 1000,
        'auto_delete_after' => null, // null = no auto-delete
    ],

    // Configuración de notificaciones
    'notifications' => [
        'enabled' => true,
        'sound' => true,
        'desktop' => true,
    ],

    // Configuración de encriptación de mensajes
    'encryption' => [
        'enabled' => env('CHAT_ENCRYPTION_ENABLED', true),
        'key' => env('CHAT_ENCRYPTION_KEY', env('APP_KEY')),
        'cipher' => env('CHAT_ENCRYPTION_CIPHER', 'AES-256-ECB'),
    ],
];
