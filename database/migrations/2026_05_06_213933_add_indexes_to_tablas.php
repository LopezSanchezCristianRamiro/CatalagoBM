<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Producto', function (Blueprint $table) {
            $table->index('idCategoria');
            $table->index('precioDescuento');
            $table->index('nombre');
            $table->index('estado');
        });

        Schema::table('FotoProducto', function (Blueprint $table) {
            $table->index('idProducto');
        });

        Schema::table('Categoria', function (Blueprint $table) {
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('Producto', function (Blueprint $table) {
            $table->dropIndex(['idCategoria']);
            $table->dropIndex(['precioDescuento']);
            $table->dropIndex(['nombre']);
            $table->dropIndex(['estado']);
        });

        Schema::table('FotoProducto', function (Blueprint $table) {
            $table->dropIndex(['idProducto']);
        });

        Schema::table('Categoria', function (Blueprint $table) {
            $table->dropIndex(['nombre']);
        });
    }
};