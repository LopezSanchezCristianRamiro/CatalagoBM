<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class PublicCatalogoController extends Controller
{
    // Calculamos el prefijo UNA sola vez aquí
    private function resolverUrlFoto(?string $urlFoto): ?string
    {
        if (!$urlFoto) return null;

        // Si ya es URL completa, no hacer nada
        if (filter_var($urlFoto, FILTER_VALIDATE_URL)) {
            return $urlFoto;
        }

        // Construir URL sin llamar Storage cada vez
        return config('app.url') . '/storage/' . $urlFoto;
    }

    public function getProductos(Request $request)
    {
        $page           = $request->query('page', 1);
        $limit          = $request->query('limit', 15);
        $search         = $request->query('search', '');
        $idCategoria    = $request->query('idCategoria');
        $soloPromociones = $request->query('soloPromociones');

        $query = Producto::with(['fotos', 'categoria']);

        // Filtro de búsqueda por nombre
        if (!empty($search)) {
            $query->where('nombre', 'LIKE', '%' . $search . '%');
        }

        // Filtro por categoría
        if (!empty($idCategoria)) {
            $query->where('idCategoria', $idCategoria);
        }

        // Filtro promociones — CORREGIDO: 1 sola query simple en vez de whereHas
        if ($soloPromociones == 1) {
            $idCatPromo = Categoria::where('nombre', 'Promociones')
                ->value('idCategoria'); // ← solo busca el ID una vez

            $query->where(function ($q) use ($idCatPromo) {
                if ($idCatPromo) {
                    $q->where('idCategoria', $idCatPromo);
                }
                $q->orWhereNotNull('precioDescuento');
            });
        }

        $productos = $query->paginate($limit, ['*'], 'page', $page);

        // Transformar URLs — CORREGIDO: sin Storage::url() en loop
        $productos->getCollection()->transform(function ($producto) {
            $producto->fotos->transform(function ($foto) {
                $foto->urlFoto = $this->resolverUrlFoto($foto->urlFoto);
                return $foto;
            });
            return $producto;
        });

        return response()->json($productos);
    }

    public function show($idProducto)
    {
        $producto = Producto::with(['fotos', 'categoria'])
            ->findOrFail($idProducto);

        $producto->fotos->transform(function ($foto) {
            $foto->urlFoto = $this->resolverUrlFoto($foto->urlFoto);
            return $foto;
        });

        return response()->json($producto);
    }
}