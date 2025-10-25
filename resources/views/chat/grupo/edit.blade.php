@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Grupo</h2>
            
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('chat.grupo.update', $chat->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-6">
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Grupo *
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre', $chat->nombre) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ingresa el nombre del grupo"
                           required>
                </div>

                <div class="mb-6">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción (Opcional)
                    </label>
                    <textarea id="descripcion" 
                              name="descripcion" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Describe el propósito del grupo">{{ old('descripcion', $chat->descripcion) }}</textarea>
                </div>

                <!-- Información del grupo -->
                <div class="mb-6 p-4 bg-gray-50 rounded-md">
                    <h3 class="font-medium text-gray-800 mb-3">Información del Grupo</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">Creado por:</span>
                            <p class="text-gray-800">{{ $chat->creador->nombre }} {{ $chat->creador->apellido }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Miembros:</span>
                            <p class="text-gray-800">{{ $chat->contarMiembros() }} miembros</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Creado:</span>
                            <p class="text-gray-800">{{ $chat->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Última actualización:</span>
                            <p class="text-gray-800">{{ $chat->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Lista de miembros actuales -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-800 mb-3">Miembros Actuales</h3>
                    <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                        @foreach($chat->usuarios as $miembro)
                            <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                        {{ substr($miembro->nombre, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">
                                            {{ $miembro->nombre }} {{ $miembro->apellido }}
                                            @if($miembro->id == $chat->creador_id)
                                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full ml-2">Creador</span>
                                            @elseif($chat->esAdministrador($miembro->id))
                                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2">Admin</span>
                                            @endif
                                        </p>
                                        @if($miembro->email)
                                            <p class="text-sm text-gray-500">{{ $miembro->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('chat.grupo.show', $chat->id) }}" 
                       class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                        Actualizar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
