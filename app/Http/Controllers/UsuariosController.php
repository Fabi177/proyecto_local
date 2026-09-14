<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarUsuarioRequest;
use Illuminate\Http\Request;

class UsuariosController extends Controller
{
    // index -> listado de elementos del recurso
    public function index() {
        return view('usuarios.index');
    }

    // show -> la pagina con los datos de un elemento puntual del recurso
    public function show() {
        dd("Show");
    }

    // create -> la pagina para crear un elemento del recurso
    public function create() {
        return view('usuarios.create');
    }

    // store -> guardar los datos de un recurso creado
    public function store(GuardarUsuarioRequest $request) {
        echo 'Validado';
    }

    // edit -> la pagina para editar datos de un elemento del recurso
    public function edit() {
        dd("Edit");
    }

    // update -> actualizar los datos de un recurso
    public function update() {
        dd("Update");
    }

    // destroy -> eliminar un elemento del recurso
    public function destroy() {
        dd("Destroy");
    }
}
