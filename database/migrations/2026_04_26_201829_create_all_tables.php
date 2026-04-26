<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla Categoria
        Schema::create('Categoria', function (Blueprint $table) {
            $table->id('idCategoria');
            $table->string('nombre', 150);
            $table->timestamps();
        });

        // Tabla Producto
        Schema::create('Producto', function (Blueprint $table) {
            $table->id('idProducto');
            $table->string('nombre', 150);
            $table->string('descripcion', 150)->nullable();
            $table->decimal('precio', 10, 2);
            $table->foreignId('idCategoria')->nullable()->constrained('Categoria', 'idCategoria')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });

        // Tabla FotoProducto
        Schema::create('FotoProducto', function (Blueprint $table) {
            $table->id('idFotoProducto');
            $table->text('urlFoto')->nullable();
            $table->foreignId('idProducto')->constrained('Producto', 'idProducto')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        // Tabla Rol
        Schema::create('Rol', function (Blueprint $table) {
            $table->id('idRol');
            $table->string('nombre', 50)->unique();
            $table->timestamps();
        });

        // Tabla Usuario (incluye password para autenticación)
        Schema::create('Usuario', function (Blueprint $table) {
            $table->id('idUsuario');
            $table->string('nombre', 150);
            $table->string('nombreUsuario', 100)->unique();
            $table->text('foto')->nullable();
            $table->string('correo', 50)->unique();
            $table->string('telefono', 50)->nullable();
            $table->string('password'); // ← imprescindible
            $table->foreignId('idRol')->nullable()->constrained('Rol', 'idRol')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });

        // Tabla Pedido
        Schema::create('Pedido', function (Blueprint $table) {
            $table->id('idPedido');
            $table->enum('estado', ['pendiente', 'pagado', 'cancelado', 'entregado'])->default('pendiente');
            $table->decimal('total', 10, 2)->nullable();
            $table->enum('tipoPago', ['contra_entrega', 'qr', 'tarjeta'])->nullable();
            $table->string('observacion', 150)->nullable();
            $table->dateTime('fechaCreacion')->default(now());
            $table->foreignId('idUsuario')->nullable()->constrained('Usuario', 'idUsuario')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });

        // Tabla DetallePedido
        Schema::create('DetallePedido', function (Blueprint $table) {
            $table->id('idDetallePedido');
            $table->foreignId('idProducto')->constrained('Producto', 'idProducto')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('cantidad');
            $table->decimal('precioUnitario', 10, 2);
            $table->decimal('subTotal', 10, 2);
            $table->foreignId('idPedido')->constrained('Pedido', 'idPedido')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        // Tabla de tokens de Sanctum (necesaria)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('DetallePedido');
        Schema::dropIfExists('Pedido');
        Schema::dropIfExists('Usuario');
        Schema::dropIfExists('Rol');
        Schema::dropIfExists('FotoProducto');
        Schema::dropIfExists('Producto');
        Schema::dropIfExists('Categoria');
    }
};