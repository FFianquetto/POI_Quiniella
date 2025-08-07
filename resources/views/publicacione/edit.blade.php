@extends('layouts.app')

@section('template_title')
    Editar Servicio
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">Editar Servicio</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('publicaciones.update', $publicacione->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('publicacione.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
