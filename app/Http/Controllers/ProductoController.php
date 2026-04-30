<?php

namespace App\Http\Controllers;

use App\Models\FotoProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        return response()->json(
            Producto::with(['categoria', 'fotos'])
                ->orderByDesc('idProducto')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
            'precio' => 'required|numeric|min:0',
            'precioDescuento' => 'nullable|numeric|min:0',
            'idCategoria' => 'required|exists:Categoria,idCategoria',
            'estado' => 'nullable|in:activado,desactivado',
            'urlFotos' => 'nullable|array',
            'urlFotos.*' => 'required|string',
        ]);

        return DB::transaction(function () use ($data) {
            $producto = Producto::create([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'precio' => $data['precio'],
                'precioDescuento' => $data['precioDescuento'] ?? null,
                'idCategoria' => $data['idCategoria'],
                'estado' => $data['estado'] ?? 'activado', // 🔥 IMPORTANTE
            ]);

            foreach (($data['urlFotos'] ?? []) as $urlFoto) {
                FotoProducto::create([
                    'urlFoto' => $urlFoto,
                    'idProducto' => $producto->idProducto,
                ]);
            }

            return response()->json([
                'message' => 'Producto creado correctamente',
                'producto' => $producto->load(['categoria', 'fotos']),
            ], 201);
        });
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:150',
            'precio' => 'required|numeric|min:0',
            'precioDescuento' => 'nullable|numeric|min:0',
            'idCategoria' => 'required|exists:Categoria,idCategoria',
            'estado' => 'nullable|in:activado,desactivado', // 🔥 FALTABA
            'urlFotos' => 'nullable|array',
            'urlFotos.*' => 'required|string',
        ]);

        return DB::transaction(function () use ($producto, $data) {
            $producto->update([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'precio' => $data['precio'],
                'precioDescuento' => $data['precioDescuento'] ?? null,
                'idCategoria' => $data['idCategoria'],
                'estado' => $data['estado'] ?? $producto->estado, // 🔥 IMPORTANTE
            ]);

            if (array_key_exists('urlFotos', $data)) {
                $producto->fotos()->delete();

                foreach (($data['urlFotos'] ?? []) as $urlFoto) {
                    FotoProducto::create([
                        'urlFoto' => $urlFoto,
                        'idProducto' => $producto->idProducto,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Producto actualizado correctamente',
                'producto' => $producto->load(['categoria', 'fotos']),
            ]);
        });
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        $producto->fotos()->delete();
        $producto->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente',
        ]);
    }
}