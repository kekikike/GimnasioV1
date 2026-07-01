<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE TAsistenciasPersonal SET estadoAsistencia = 'presente' WHERE estadoAsistencia NOT IN ('presente', 'falta')");

        DB::statement("ALTER TABLE TAsistenciasPersonal ADD CONSTRAINT chk_estado_asistencia CHECK (estadoAsistencia IN ('presente', 'falta'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE TAsistenciasPersonal DROP CONSTRAINT chk_estado_asistencia");
    }
};
