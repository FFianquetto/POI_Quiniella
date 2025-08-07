@extends('layouts.app')

@section('template_title')
    {{ __('Buscar Usuarios') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Buscar Usuarios para Chatear') }}
                        </span>
                        <div>
                            <a href="{{ route('chats.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Volver a Chats
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($usuarios->count() > 0)
                        <div class="row">
                            @foreach ($usuarios as $usuarioBuscar)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="fa fa-user-circle fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="card-title">{{ $usuarioBuscar->nombre }}</h5>
                                            <p class="card-text text-muted">{{ $usuarioBuscar->correo }}</p>
                                            <p class="card-text">
                                                <small class="text-muted">Edad: {{ $usuarioBuscar->edad }} años</small>
                                            </p>
                                            <form action="{{ route('chats.crear') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="usuario_id" value="{{ $usuarioBuscar->id }}">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-comment"></i> Iniciar Chat
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-users fa-3x text-muted mb-3"></i>
                            <h5>No hay otros usuarios disponibles</h5>
                            <p class="text-muted">No hay usuarios registrados para chatear</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
