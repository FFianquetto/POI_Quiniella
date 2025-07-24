<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\RegistroRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

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
        Registro::create($request->validated());

        return Redirect::route('registros.index')
            ->with('success', 'Registro created successfully.');
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

        return Redirect::route('registros.index')
            ->with('success', 'Registro updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Registro::find($id)->delete();

        return Redirect::route('registros.index')
            ->with('success', 'Registro deleted successfully');
    }
}
