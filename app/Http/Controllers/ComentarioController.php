<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ComentarioRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ComentarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $comentarios = Comentario::paginate();

        return view('comentario.index', compact('comentarios'))
            ->with('i', ($request->input('page', 1) - 1) * $comentarios->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $comentario = new Comentario();

        return view('comentario.create', compact('comentario'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComentarioRequest $request): RedirectResponse
    {
        Comentario::create($request->validated());

        return Redirect::route('comentarios.index')
            ->with('success', 'Comentario created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $comentario = Comentario::find($id);

        return view('comentario.show', compact('comentario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $comentario = Comentario::find($id);

        return view('comentario.edit', compact('comentario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ComentarioRequest $request, Comentario $comentario): RedirectResponse
    {
        $comentario->update($request->validated());

        return Redirect::route('comentarios.index')
            ->with('success', 'Comentario updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Comentario::find($id)->delete();

        return Redirect::route('comentarios.index')
            ->with('success', 'Comentario deleted successfully');
    }
}
