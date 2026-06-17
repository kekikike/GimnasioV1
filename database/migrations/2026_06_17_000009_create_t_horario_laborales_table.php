<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('THorarioLaborales', function (Blueprint $table) {
            $table->increments('idHorario');
            $table->integer('carnetEmpleado');
            $table->string('diaSemana', 20);
            $table->time('horaEntradaEsperada');
            $table->time('horaSalidaEsperada');
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('THorarioLaborales');
    }
};
