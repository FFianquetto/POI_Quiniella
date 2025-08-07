@extends('layouts.app')

@section('template_title')
    Publicar Servicio
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">Publicar Nuevo Servicio</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('publicaciones.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('publicacione.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
