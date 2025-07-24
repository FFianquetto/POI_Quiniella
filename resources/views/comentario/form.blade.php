<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="registro_id_emisor" class="form-label">{{ __('Registro Id Emisor') }}</label>
            <input type="text" name="registro_id_emisor" class="form-control @error('registro_id_emisor') is-invalid @enderror" value="{{ old('registro_id_emisor', $comentario?->registro_id_emisor) }}" id="registro_id_emisor" placeholder="Registro Id Emisor">
            {!! $errors->first('registro_id_emisor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="registro_id_receptor" class="form-label">{{ __('Registro Id Receptor') }}</label>
            <input type="text" name="registro_id_receptor" class="form-control @error('registro_id_receptor') is-invalid @enderror" value="{{ old('registro_id_receptor', $comentario?->registro_id_receptor) }}" id="registro_id_receptor" placeholder="Registro Id Receptor">
            {!! $errors->first('registro_id_receptor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="mensaje" class="form-label">{{ __('Mensaje') }}</label>
            <input type="text" name="mensaje" class="form-control @error('mensaje') is-invalid @enderror" value="{{ old('mensaje', $comentario?->mensaje) }}" id="mensaje" placeholder="Mensaje">
            {!! $errors->first('mensaje', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="link" class="form-label">{{ __('Link') }}</label>
            <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link', $comentario?->link) }}" id="link" placeholder="Link">
            {!! $errors->first('link', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>