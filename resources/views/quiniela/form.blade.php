<div class="box box-info padding-1">
    <div class="box-body">
        
        <div class="form-group">
            {{ Form::label('nombre') }}
            {{ Form::text('nombre', $quiniela->nombre, ['class' => 'form-control' . ($errors->has('nombre') ? ' is-invalid' : ''), 'placeholder' => 'Nombre de la quiniela']) }}
            {!! $errors->first('nombre', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('descripcion') }}
            {{ Form::textarea('descripcion', $quiniela->descripcion, ['class' => 'form-control' . ($errors->has('descripcion') ? ' is-invalid' : ''), 'placeholder' => 'Descripción de la quiniela']) }}
            {!! $errors->first('descripcion', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('precio_entrada') }}
            {{ Form::number('precio_entrada', $quiniela->precio_entrada, ['class' => 'form-control' . ($errors->has('precio_entrada') ? ' is-invalid' : ''), 'placeholder' => 'Precio de entrada', 'step' => '0.01', 'min' => '0']) }}
            {!! $errors->first('precio_entrada', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('max_participantes') }}
            {{ Form::number('max_participantes', $quiniela->max_participantes, ['class' => 'form-control' . ($errors->has('max_participantes') ? ' is-invalid' : ''), 'placeholder' => 'Máximo de participantes (opcional)', 'min' => '1']) }}
            {!! $errors->first('max_participantes', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('fecha_limite') }}
            {{ Form::datetime-local('fecha_limite', $quiniela->fecha_limite ? $quiniela->fecha_limite->format('Y-m-d\TH:i') : null, ['class' => 'form-control' . ($errors->has('fecha_limite') ? ' is-invalid' : ''), 'placeholder' => 'Fecha límite']) }}
            {!! $errors->first('fecha_limite', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>
