@extends('layouts.app')

@section('title', 'Configuración de Encriptación')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🔐 Configuración de Encriptación</h1>
                    <p class="text-gray-600 mt-2">Gestiona la seguridad y privacidad de los datos de la aplicación</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Estado actual</div>
                    <div class="text-lg font-semibold {{ $config['enabled'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $config['enabled'] ? '✅ Habilitada' : '❌ Deshabilitada' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">💬</div>
                    <div>
                        <div class="text-sm text-gray-500">Mensajes</div>
                        <div class="text-2xl font-bold text-gray-900">{{ \App\Models\Mensaje::count() }}</div>
                        <div class="text-xs {{ $config['data_types']['messages']['enabled'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $config['data_types']['messages']['enabled'] ? 'Encriptados' : 'Sin encriptar' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">💭</div>
                    <div>
                        <div class="text-sm text-gray-500">Comentarios</div>
                        <div class="text-2xl font-bold text-gray-900">{{ \App\Models\Comentario::count() }}</div>
                        <div class="text-xs {{ $config['data_types']['comments']['enabled'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $config['data_types']['comments']['enabled'] ? 'Encriptados' : 'Sin encriptar' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">👥</div>
                    <div>
                        <div class="text-sm text-gray-500">Usuarios</div>
                        <div class="text-2xl font-bold text-gray-900">{{ \App\Models\Registro::count() }}</div>
                        <div class="text-xs text-green-600">Contraseñas hasheadas</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">⚙️ Configuración de Encriptación</h2>
            
            <form action="{{ route('admin.encryption.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Encriptación Global -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Configuración Global</h3>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Encriptación Global</label>
                                <p class="text-xs text-gray-500">Habilitar/deshabilitar toda la encriptación</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enabled" value="1" 
                                       {{ $config['enabled'] ? 'checked' : '' }} 
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Encriptar Sesiones</label>
                                <p class="text-xs text-gray-500">Encriptar datos de sesión</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="session_encrypt" value="1" 
                                       {{ $config['session']['encrypt'] ? 'checked' : '' }} 
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Encriptar Cookies</label>
                                <p class="text-xs text-gray-500">Encriptar cookies del navegador</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="cookie_encrypt" value="1" 
                                       {{ $config['cookies']['encrypt'] ? 'checked' : '' }} 
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Encriptación por Tipo -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Tipos de Datos</h3>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Mensajes de Chat</label>
                                <p class="text-xs text-gray-500">Encriptar contenido de mensajes</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="encrypt_messages" value="1" 
                                       {{ $config['data_types']['messages']['enabled'] ? 'checked' : '' }} 
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Comentarios</label>
                                <p class="text-xs text-gray-500">Encriptar contenido de comentarios</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="encrypt_comments" value="1" 
                                       {{ $config['data_types']['comments']['enabled'] ? 'checked' : '' }} 
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Metadatos de Archivos</label>
                                <p class="text-xs text-gray-500">Encriptar nombres de archivos</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="encrypt_media_metadata" value="1" 
                                       {{ $config['data_types']['media_files']['enabled'] ? 'checked' : '' }} 
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        💾 Guardar Configuración
                    </button>
                </div>
            </form>
        </div>

        <!-- Migración de Datos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🔄 Migración de Datos</h2>
            <p class="text-gray-600 mb-4">Migra datos existentes para usar encriptación. <strong>⚠️ Haz una copia de seguridad antes de ejecutar.</strong></p>
            
            <form action="{{ route('admin.encryption.migrate') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Migración</label>
                        <select name="migration_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all">Todos los datos</option>
                            <option value="passwords">Solo contraseñas</option>
                            <option value="messages">Solo mensajes</option>
                            <option value="comments">Solo comentarios</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="dry_run" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Solo simulación</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        🚀 Ejecutar Migración
                    </button>
                </div>
            </form>

            @if(session('migration_output'))
                <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                    <h3 class="font-semibold text-gray-800 mb-2">Salida de la migración:</h3>
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ session('migration_output') }}</pre>
                </div>
            @endif
        </div>

        <!-- Información Adicional -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">ℹ️ Información Importante</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• La encriptación usa el algoritmo AES-256-CBC</li>
                <li>• Las contraseñas se almacenan con hash bcrypt</li>
                <li>• Los datos encriptados no se pueden recuperar sin la clave</li>
                <li>• Haz siempre una copia de seguridad antes de migrar datos</li>
                <li>• La migración puede tomar tiempo dependiendo de la cantidad de datos</li>
            </ul>
        </div>
    </div>
</div>
@endsection
