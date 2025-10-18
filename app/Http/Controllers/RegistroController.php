<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\RegistroRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Database\QueryException;

class RegistroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $registros = Registro::paginate();

        return view('registro.index', compact('registros'))
            ->with('i', ($request->input('page', 1) - 1) * $registros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $registro = new Registro();

        return view('registro.create', compact('registro'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegistroRequest $request): RedirectResponse
    {
        try {
            $registro = Registro::create($request->validated());

            // Redirigir al login con mensaje de éxito
            return Redirect::route('auth.login')
                ->with('success', '¡Registro exitoso! Ahora puedes iniciar sesión con tu correo y contraseña.');
                
        } catch (QueryException $e) {
            // Si ocurre un error de integridad (correo duplicado), redirigir con error
            if ($e->getCode() == 23000) {
                return Redirect::back()
                    ->withInput()
                    ->with('error', 'Este correo electrónico ya está registrado. Por favor usa otro correo.');
            }
            
            // Para otros errores de base de datos
            return Redirect::back()
                ->withInput()
                ->with('error', 'Ocurrió un error al registrar. Por favor intenta de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $registro = Registro::find($id);

        return view('registro.show', compact('registro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $registro = Registro::find($id);

        return view('registro.edit', compact('registro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RegistroRequest $request, Registro $registro): RedirectResponse
    {
        $registro->update($request->validated());

        return Redirect::route('registros.index');
    }

    public function destroy($id): RedirectResponse
    {
        Registro::find($id)->delete();

        return Redirect::route('registros.index');
    }
}
