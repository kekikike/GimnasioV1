<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TControlAsistencias', function (Blueprint $table) {
            $table->increments('idAsistencia');
            $table->integer('carnetEmpleado');
            $table->date('fecha');
            $table->time('horaEntrada');
            $table->time('horaSalida')->nullable();
            $table->string('estadoAsistencia', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TControlAsistencias');
    }
};
