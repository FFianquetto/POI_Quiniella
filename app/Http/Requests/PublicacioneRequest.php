<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicacioneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registro_id' => 'nullable|exists:registros,id',
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'conversacion_id' => 'nullable|exists:conversaciones,id',
        ];
    }
}
