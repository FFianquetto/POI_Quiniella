<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        @if(!isset($emisor) || !isset($receptor))
            <div class="form-group mb-2 mb20">
                <label for="registro_id_emisor" class="form-label">Remitente</label>
                <select name="registro_id_emisor" class="form-control @error('registro_id_emisor') is-invalid @enderror" id="registro_id_emisor">
                    <option value="">Selecciona el remitente</option>
                    @foreach(\App\Models\Registro::all() as $registro)
                        <option value="{{ $registro->id }}" {{ old('registro_id_emisor', $comentario?->registro_id_emisor) == $registro->id ? 'selected' : '' }}>
                            {{ $registro->nombre }} ({{ $registro->correo }})
                        </option>
                    @endforeach
                </select>
                {!! $errors->first('registro_id_emisor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
            </div>
            
            <div class="form-group mb-2 mb20">
                <label for="registro_id_receptor" class="form-label">Destinatario</label>
                <select name="registro_id_receptor" class="form-control @error('registro_id_receptor') is-invalid @enderror" id="registro_id_receptor">
                    <option value="">Selecciona el destinatario</option>
                    @foreach(\App\Models\Registro::all() as $registro)
                        <option value="{{ $registro->id }}" {{ old('registro_id_receptor', $comentario?->registro_id_receptor) == $registro->id ? 'selected' : '' }}>
                            {{ $registro->nombre }} ({{ $registro->correo }})
                        </option>
                    @endforeach
                </select>
                {!! $errors->first('registro_id_receptor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
            </div>
        @endif

        <div class="form-group mb-2 mb20">
            <label for="mensaje" class="form-label">Mensaje</label>
            <textarea name="mensaje" class="form-control @error('mensaje') is-invalid @enderror" 
                      id="mensaje" rows="4" 
                      placeholder="@if(isset($receptor))Escribe tu mensaje para {{ $receptor->nombre }}...@elseEscribe tu mensaje...@endif">{{ old('mensaje', $comentario?->mensaje) }}</textarea>
            {!! $errors->first('mensaje', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="link" class="form-label">Link (opcional)</label>
            <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" 
                   value="{{ old('link', $comentario?->link) }}" id="link" placeholder="Link opcional">
            {!! $errors->first('link', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">
            @if(isset($emisor) && isset($receptor))
                Enviar Mensaje
            @else
                Enviar
            @endif
        </button>
    </div>
</div>