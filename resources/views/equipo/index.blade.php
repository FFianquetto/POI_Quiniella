@extends('layouts.app')

@section('template_title')
    {{ __('Equipos') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Equipos de Fútbol') }}
                        </span>
                    </div>
                </div>
                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <p>{{ $message }}</p>
                    </div>
                @endif

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead">
                                <tr>
                                    <th>No</th>
                                    <th>Nombre</th>
                                    <th>Abreviación</th>
                                    <th>Ciudad</th>
                                    <th>Liga</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($equipos as $index => $equipo)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $equipo->nombre }}</td>
                                        <td><span class="badge bg-primary">{{ $equipo->abreviacion }}</span></td>
                                        <td>{{ $equipo->ciudad }}</td>
                                        <td>{{ $equipo->liga }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ route('equipos.show',$equipo->id) }}">
                                                <i class="fa fa-fw fa-eye"></i> Ver Detalles
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {!! $equipos->links() !!}
        </div>
    </div>
</div>
@endsection
