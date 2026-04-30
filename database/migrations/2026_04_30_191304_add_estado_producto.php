<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE Producto 
            ADD estado ENUM('activado','desactivado') 
            DEFAULT 'activado'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE Producto 
            DROP COLUMN estado
        ");
    }
};
