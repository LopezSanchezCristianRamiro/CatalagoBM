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

        $query = Producto::with(['fotos', 'categoria']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', '%' . $search . '%')
                  ->orWhere('descripcion', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($idCategoria)) {
            $query->where('idCategoria', $idCategoria);
        }

        if ($soloPromociones == 1) {
            $query->where(function ($q) {
                $q->whereHas('categoria', function ($qCat) {
                    $qCat->where('nombre', 'Promos');
                })->orWhereNotNull('precioDescuento');
            });
        }

        $productos = $query->paginate($limit, ['*'], 'page', $page);

        // Mutar las URLs de las fotos para que sean absolutas
        $productos->getCollection()->transform(function ($producto) {
            $producto->fotos->transform(function ($foto) {
                if ($foto->urlFoto && !filter_var($foto->urlFoto, FILTER_VALIDATE_URL)) {
                    // Convertimos la ruta a URL absoluta
                    // Primero intentamos con Storage::url, y lo envolvemos con url() para dominio base
                    // Si el path no es válido para Storage::url, usamos asset()
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
}
