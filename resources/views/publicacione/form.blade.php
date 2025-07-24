<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="titulo" class="form-label">{{ __('Título') }}</label>
            <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $publicacione?->titulo) }}" id="titulo" placeholder="Título de la publicación">
            {!! $errors->first('titulo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        
        <div class="form-group mb-2 mb20">
            <label for="contenido" class="form-label">{{ __('Contenido') }}</label>
            <textarea name="contenido" class="form-control @error('contenido') is-invalid @enderror" id="contenido" rows="5" placeholder="Contenido de la publicación">{{ old('contenido', $publicacione?->contenido) }}</textarea>
            {!! $errors->first('contenido', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        @if(isset($registroId))
            <input type="hidden" name="registro_id" value="{{ $registroId }}">
        @endif

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>