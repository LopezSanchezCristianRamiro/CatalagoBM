<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        return response()->json(
            Categoria::orderByDesc('idCategoria')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
        ]);

        $categoria = Categoria::create($data);

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'categoria' => $categoria,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:150',
        ]);

        $categoria->update($data);

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'categoria' => $categoria,
        ]);
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        if ($categoria->productos()->count() > 0) {
            return response()->json([
                'message' => 'No puedes eliminar una categoría con productos asignados',
            ], 422);
        }

        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
}