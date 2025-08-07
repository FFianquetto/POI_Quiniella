@extends('layouts.app')

@section('template_title')
    {{ __('Quinielas') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Quinielas Disponibles') }}
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
                                    <th>Partido</th>
                                    <th>Fecha</th>
                                    <th>Participantes</th>
                                    <th>Fecha Límite</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quinielas as $index => $quiniela)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $quiniela->partido->equipoLocal->nombre }} vs {{ $quiniela->partido->equipoVisitante->nombre }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $quiniela->partido->liga }}</small>
                                        </td>
                                        <td>{{ $quiniela->partido->fecha_hora->format('d/m/Y H:i') }}</td>
                                        <td>{{ $quiniela->participantes->count() }}</td>
                                        <td>{{ $quiniela->fecha_limite->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($quiniela->estado === 'activa')
                                                <span class="badge bg-success">Activa</span>
                                            @elseif($quiniela->estado === 'cerrada')
                                                <span class="badge bg-warning">Cerrada</span>
                                            @else
                                                <span class="badge bg-secondary">Finalizada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ route('quinielas.show',$quiniela->id) }}">
                                                <i class="fa fa-fw fa-eye"></i> Ver Quiniela
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {!! $quinielas->links() !!}
        </div>
    </div>
</div>
@endsection
