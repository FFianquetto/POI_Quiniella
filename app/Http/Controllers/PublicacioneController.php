<?php

namespace App\Http\Controllers;

use App\Models\Publicacione;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PublicacioneRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PublicacioneController extends Controller
{
    public function index(Request $request): View
    {
        $publicaciones = Publicacione::with('autor')->paginate();
        $usuarioRegistrado = session('usuario_registrado');
        $registroId = session('registro_id');

        return view('publicacione.index', compact('publicaciones', 'usuarioRegistrado', 'registroId'))
            ->with('i', ($request->input('page', 1) - 1) * $publicaciones->perPage());
    }

    public function create(): View
    {
        $publicacione = new Publicacione();
        $registroId = session('registro_id');

        return view('publicacione.create', compact('publicacione', 'registroId'));
    }

    public function store(PublicacioneRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        if (!isset($data['registro_id']) || empty($data['registro_id'])) {
            $registroId = session('registro_id');
            if (!$registroId) {
                $primerRegistro = \App\Models\Registro::first();
                if ($primerRegistro) {
                    $data['registro_id'] = $primerRegistro->id;
                } else {
                    return Redirect::route('registros.create')
                        ->with('error', 'Debes registrarte primero para crear publicaciones.');
                }
            } else {
                $data['registro_id'] = $registroId;
            }
        }
        
        Publicacione::create($data);

        return Redirect::route('publicaciones.index');
    }

    public function show($id): View
    {
        $publicacione = Publicacione::find($id);
        return view('publicacione.show', compact('publicacione'));
    }

    public function edit($id): View
    {
        $publicacione = Publicacione::find($id);
        return view('publicacione.edit', compact('publicacione'));
    }

    public function update(PublicacioneRequest $request, Publicacione $publicacione): RedirectResponse
    {
        $publicacione->update($request->validated());
        return Redirect::route('publicaciones.index');
    }

    public function destroy($id): RedirectResponse
    {
        Publicacione::find($id)->delete();
        return Redirect::route('publicaciones.index');
    }
}
