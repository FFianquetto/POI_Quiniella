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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $publicaciones = Publicacione::paginate();

        return view('publicacione.index', compact('publicaciones'))
            ->with('i', ($request->input('page', 1) - 1) * $publicaciones->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $publicacione = new Publicacione();

        return view('publicacione.create', compact('publicacione'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PublicacioneRequest $request): RedirectResponse
    {
        Publicacione::create($request->validated());

        return Redirect::route('publicaciones.index')
            ->with('success', 'Publicacione created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $publicacione = Publicacione::find($id);

        return view('publicacione.show', compact('publicacione'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $publicacione = Publicacione::find($id);

        return view('publicacione.edit', compact('publicacione'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PublicacioneRequest $request, Publicacione $publicacione): RedirectResponse
    {
        $publicacione->update($request->validated());

        return Redirect::route('publicaciones.index')
            ->with('success', 'Publicacione updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Publicacione::find($id)->delete();

        return Redirect::route('publicaciones.index')
            ->with('success', 'Publicacione deleted successfully');
    }
}
