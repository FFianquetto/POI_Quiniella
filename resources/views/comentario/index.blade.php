@extends('layouts.app')

@section('template_title')
    Comentarios
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Comentarios') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('comentarios.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                              </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
                                        
									<th >Registro Id Emisor</th>
									<th >Registro Id Receptor</th>
									<th >Mensaje</th>
									<th >Link</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($comentarios as $comentario)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
										<td >{{ $comentario->registro_id_emisor }}</td>
										<td >{{ $comentario->registro_id_receptor }}</td>
										<td >{{ $comentario->mensaje }}</td>
										<td >{{ $comentario->link }}</td>

                                            <td>
                                                <form action="{{ route('comentarios.destroy', $comentario->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('comentarios.show', $comentario->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('comentarios.edit', $comentario->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {!! $comentarios->withQueryString()->links() !!}
            </div>
        </div>
    </div>
@endsection
