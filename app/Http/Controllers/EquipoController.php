<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipoController extends Controller
{
    public function index(): View
    {
        $equipos = Equipo::orderBy('nombre')->paginate(20);
        return view('equipo.index', compact('equipos'));
    }

    public function show(Equipo $equipo): View
    {
        return view('equipo.show', compact('equipo'));
    }
}
