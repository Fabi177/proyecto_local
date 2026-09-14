<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TareasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tareas = Tarea::all();

        return view('tareas.index', [
            'tareas' => $tareas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tareas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'descripcion' => 'required|min:4|max:255',
            'prioridad' => [
                'required',
                Rule::in(['secundario', 'normal', 'urgente']),
            ],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        Tarea::create([
            'descripcion' => $datos['descripcion'],
            'prioridad' => $datos['prioridad'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
        ]);

        return redirect()->route('tareas.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tarea = Tarea::findOrFail($id);

        return view('tareas.show', [
            'tarea' => $tarea,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tarea = Tarea::findOrFail($id);

        return view('tareas.edit', [
            'tarea' => $tarea,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $datos = $request->validate([
            'descripcion' => 'required|min:4|max:255',
            'prioridad' => [
                'required',
                Rule::in(['secundario', 'normal', 'urgente']),
            ],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        Tarea::where('id', $id)
            ->update([
                'descripcion' => $datos['descripcion'],
                'prioridad' => $datos['prioridad'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
            ]);

        return redirect()->route('tareas.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tarea = Tarea::findOrFail($id);

        $tarea->delete();

        return redirect()->route('tareas.index');
    }
}
