@extends('layouts.app')

@section('content')
<style>
.member-card {
    transition: all 0.3s ease;
    transform: translateY(0);
}

.member-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.member-card.selected {
    border-color: #2E7D32 !important;
    background-color: #f1f8e9 !important;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
}

.add-button div {
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.member-card:hover .add-button div {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.member-card.selected .add-button div {
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.gradient-avatar {
    background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%);
}

/* Grid responsive personalizado - 2 COLUMNAS */
.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 640px) {
    .sm\\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}

/* Asegurar que las tarjetas tengan el mismo tamaño */
.member-card {
    min-height: 100px;
    display: flex;
    flex-direction: column;
    width: 50%;
}

/* Forzar que el grid funcione correctamente */
.grid {
    display: grid !important;
    gap: 1.5rem;
}

/* Los estilos de botones ahora están en el archivo SCSS _botones.scss */
</style>

<div class="container mx-auto px-6 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Crear Nuevo Grupo</h2>
            
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('chat.grupo.store') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Grupo *
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre') }}"
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
                              placeholder="Describe el propósito del grupo">{{ old('descripcion') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Miembros *
                    </label>
                    <p class="text-sm text-gray-600 mb-4">
                        Selecciona al menos 2 miembros para crear el grupo (mínimo 3 miembros incluyéndote)
                    </p>
                    
                    @if($usuarios->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($usuarios as $usuario)
                                <div class="member-card bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-green-500 transition-all duration-200 cursor-pointer" 
                                     data-user-id="{{ $usuario->id }}"
                                     onclick="toggleMember({{ $usuario->id }})">
                                    <div class="flex flex-col items-center text-center space-y-4">
                                        <div class="w-16 h-16 gradient-avatar rounded-full flex items-center justify-center text-white font-semibold text-2xl shadow-lg">
                                            {{ substr($usuario->nombre, 0, 1) }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800 text-lg">{{ $usuario->nombre }} {{ $usuario->apellido }}</h4>
                                            @if($usuario->email)
                                                <p class="text-sm text-gray-500 mt-1">{{ $usuario->email }}</p>
                                            @endif
                                        </div>
                                        <div class="add-button">
                                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 border-2 border-gray-300">
                                                <i class="fas fa-plus text-gray-500 text-lg"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="checkbox" 
                                           id="usuario_{{ $usuario->id }}" 
                                           name="miembros[]" 
                                           value="{{ $usuario->id }}"
                                           class="hidden">
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Contador de miembros seleccionados -->
                        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-green-800">
                                    Miembros seleccionados: <span id="member-count" class="font-bold">0</span>
                                </span>
                                <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                    Mínimo requerido: 2
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <i class="fas fa-users text-gray-400 text-5xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">No hay usuarios disponibles</h3>
                            <p class="text-gray-500 text-sm">No hay usuarios disponibles para agregar al grupo.</p>
                        </div>
                    @endif
                </div>

                <div class="botones-container">
                    <a href="{{ route('chat.index') }}" class="btn-secundario">
                        <i class="fas fa-times"></i>Cancelar
                    </a>
                    <button type="submit" id="crear-grupo-btn" class="btn-principal">
                        <i class="fas fa-users"></i>Crear Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitButton = document.getElementById('crear-grupo-btn');
    
    function updateSubmitButton() {
        const checkedCount = document.querySelectorAll('input[name="miembros[]"]:checked').length;
        const memberCountElement = document.getElementById('member-count');
        
        if (memberCountElement) {
            memberCountElement.textContent = checkedCount;
        }
        
        if (checkedCount >= 2) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed', 'disabled:from-gray-400', 'disabled:to-gray-500');
            submitButton.classList.add('from-green-600', 'to-green-700');
            submitButton.style.display = 'block';
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed', 'disabled:from-gray-400', 'disabled:to-gray-500');
            submitButton.classList.remove('from-green-600', 'to-green-700');
        }
    }
    
    // Verificar estado inicial
    updateSubmitButton();
});

function toggleMember(userId) {
    const checkbox = document.getElementById(`usuario_${userId}`);
    const card = document.querySelector(`[data-user-id="${userId}"]`);
    const addButton = card.querySelector('.add-button');
    const icon = addButton.querySelector('i');
    const buttonBg = addButton.querySelector('div');
    
    // Toggle checkbox
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        // Usuario seleccionado - Botón NEGATIVO (quitar)
        card.classList.remove('border-gray-200');
        card.classList.add('border-green-500', 'bg-green-50', 'selected');
        buttonBg.classList.remove('bg-gray-100', 'border-gray-300');
        buttonBg.classList.add('bg-red-500', 'border-red-500');
        icon.classList.remove('fa-plus', 'text-gray-500');
        icon.classList.add('fa-times', 'text-white');
    } else {
        // Usuario deseleccionado - Botón POSITIVO (añadir)
        card.classList.remove('border-green-500', 'bg-green-50', 'selected');
        card.classList.add('border-gray-200');
        buttonBg.classList.remove('bg-red-500', 'border-red-500');
        buttonBg.classList.add('bg-gray-100', 'border-gray-300');
        icon.classList.remove('fa-times', 'text-white');
        icon.classList.add('fa-plus', 'text-gray-500');
    }
    
    // Actualizar contador y botón de envío
    updateSubmitButton();
}

function updateSubmitButton() {
    const checkedCount = document.querySelectorAll('input[name="miembros[]"]:checked').length;
    const memberCountElement = document.getElementById('member-count');
    const submitButton = document.getElementById('crear-grupo-btn');
    
    if (memberCountElement) {
        memberCountElement.textContent = checkedCount;
    }
    
    if (checkedCount >= 2) {
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed', 'disabled:from-gray-400', 'disabled:to-gray-500');
        submitButton.classList.add('from-green-600', 'to-green-700');
        submitButton.style.display = 'block';
    } else {
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed', 'disabled:from-gray-400', 'disabled:to-gray-500');
        submitButton.classList.remove('from-green-600', 'to-green-700');
    }
}
</script>
@endsection
