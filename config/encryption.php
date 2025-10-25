<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Encriptación
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar las opciones de encriptación para diferentes
    | tipos de datos en la aplicación
    |
    */

    // Habilitar/deshabilitar encriptación globalmente
    'enabled' => env('ENCRYPTION_ENABLED', true),

    // Configuración específica por tipo de dato
    'data_types' => [
        // Mensajes del chat
        'messages' => [
            'enabled' => env('ENCRYPT_MESSAGES', true),
            'fields' => ['contenido'], // Campos a encriptar
        ],

        // Comentarios
        'comments' => [
            'enabled' => env('ENCRYPT_COMMENTS', true),
            'fields' => ['contenido'],
        ],

        // Archivos multimedia (metadatos)
        'media_files' => [
            'enabled' => env('ENCRYPT_MEDIA_METADATA', false),
            'fields' => ['archivo_nombre'], // Solo metadatos, no el archivo en sí
        ],

        // Datos de usuario sensibles
        'user_data' => [
            'enabled' => env('ENCRYPT_USER_DATA', false),
            'fields' => ['nombre'], // Solo si es necesario
        ],
    ],

    // Configuración de claves de encriptación
    'keys' => [
        // Usar claves diferentes para diferentes tipos de datos
        'messages' => env('ENCRYPTION_KEY_MESSAGES', env('APP_KEY')),
        'comments' => env('ENCRYPTION_KEY_COMMENTS', env('APP_KEY')),
        'default' => env('APP_KEY'),
    ],

    // Configuración de algoritmo de encriptación
    'cipher' => env('ENCRYPTION_CIPHER', 'AES-256-CBC'),

    // Configuración de sesiones
    'session' => [
        'encrypt' => env('SESSION_ENCRYPT', true),
    ],

    // Configuración de cookies
    'cookies' => [
        'encrypt' => env('COOKIE_ENCRYPT', true),
    ],
];
