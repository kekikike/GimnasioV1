<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TAsistenciasPersonal', function (Blueprint $table) {
            $table->id('idAsistencia');
            $table->integer('carnetEmpleado');
            $table->dateTime('fechaHoraEntrada');
            $table->dateTime('fechaHoraSalida')->nullable();
            $table->string('estadoAsistencia', 20)->default('Puntual');
            $table->boolean('estadoA')->default(1);

            $table->unsignedInteger('usuarioA');
            $table->timestamp('fechaA')->useCurrent();

            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TAsistenciasPersonal');
    }
};