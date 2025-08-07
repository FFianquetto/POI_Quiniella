<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para el sistema de chats
    |
    */

    // Tipos de archivos permitidos
    'allowed_file_types' => [
        'imagen' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'archivo' => ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar']
    ],

    // Tamaño máximo de archivo (en bytes)
    'max_file_size' => 10 * 1024 * 1024, // 10MB

    // Duración máxima de grabación (en segundos)
    'max_recording_duration' => 300, // 5 minutos

    // Configuración de almacenamiento
    'storage' => [
        'disk' => 'public',
        'path' => 'chat_archivos'
    ]
];
