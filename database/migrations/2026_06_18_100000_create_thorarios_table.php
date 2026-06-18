<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('THorarios', function (Blueprint $table) {
            $table->id('idHorario');
            $table->integer('carnetEmpleado');
            $table->enum('diaSemana', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
            $table->time('horaEntrada');
            $table->time('horaSalida');
            $table->boolean('estado')->default(true);

            $table->unsignedInteger('usuarioA');
            $table->ipAddress('ipA')->nullable();
            $table->timestamp('fechaA')->useCurrent();
            $table->timestamp('fechaM')->useCurrentOnUpdate()->nullable();

            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('THorarios');
    }
};