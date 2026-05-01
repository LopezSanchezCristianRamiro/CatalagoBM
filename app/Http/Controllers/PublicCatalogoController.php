<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicCatalogoController extends Controller
{
    public function getProductos(Request $request)
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 15);
        $search = $request->query('search', '');
        $idCategoria = $request->query('idCategoria');
        $soloPromociones = $request->query('soloPromociones');
        $noPaginate = $request->boolean('all'); // ?all=1 para traer todo
        // Trae todos: activados y desactivados
        $query = Producto::with(['fotos', 'categoria']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($idCategoria)) {
            $query->where('idCategoria', $idCategoria);
        }

        if ($soloPromociones == 1) {
            $query->where(function ($q) {
                $q->whereHas('categoria', function ($qCat) {
                    $qCat->where('nombre', 'Promociones');
                })->orWhereNotNull('precioDescuento');
            });
        }
        if ($noPaginate) {
            $productos = $query->get();
        } else {
            $productos = $query->paginate($limit, ['*'], 'page', $page);
        }
        $productos->getCollection()->transform(function ($producto) {
            $producto->fotos->transform(function ($foto) {
                if ($foto->urlFoto && !filter_var($foto->urlFoto, FILTER_VALIDATE_URL)) {
                    try {
                        $foto->urlFoto = url(Storage::url($foto->urlFoto));
                    } catch (\Exception $e) {
                        $foto->urlFoto = asset($foto->urlFoto);
                    }
                }

                return $foto;
            });

            return $producto;
        });

        return response()->json($productos);
    }

    public function show($idProducto)
    {
        // También permite ver desactivados, pero el frontend bloqueará compra
        $producto = Producto::with(['fotos', 'categoria'])
            ->findOrFail($idProducto);

        $producto->fotos->transform(function ($foto) {
            if ($foto->urlFoto && !filter_var($foto->urlFoto, FILTER_VALIDATE_URL)) {
                try {
                    $foto->urlFoto = url(Storage::url($foto->urlFoto));
                } catch (\Exception $e) {
                    $foto->urlFoto = asset($foto->urlFoto);
                }
            }

            return $foto;
        });

        return response()->json($producto);
    }
}