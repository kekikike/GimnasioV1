<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TAsistenciasPersonal', function (Blueprint $table) {
            $table->unsignedInteger('idHorario')->nullable()->after('carnetEmpleado');
            $table->string('estadoEntrada', 20)->nullable()->after('fechaHoraSalida');
            $table->string('estadoSalida', 20)->nullable()->after('estadoEntrada');

            $table->foreign('idHorario')->references('idHorario')->on('THorarioLaborales')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('TAsistenciasPersonal', function (Blueprint $table) {
            $table->dropForeign(['idHorario']);
            $table->dropColumn(['idHorario', 'estadoEntrada', 'estadoSalida']);
        });
    }
};
