<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TClaseGrupales', function (Blueprint $table) {
            $table->increments('idClaseGrupal');
            $table->unsignedInteger('idActividad');
            $table->integer('carnetEmpleado');
            $table->unsignedInteger('idSucursal');
            $table->date('fecha');
            $table->time('horaInicio');
            $table->time('horaFin');
            $table->integer('cupoMaximo');
            $table->integer('cupoDisponible');
            $table->enum('estadoClase', ['Programada', 'Cursandose', 'Cancelada']);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idActividad')->references('idActividad')->on('TActividades');
            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados');
            $table->foreign('idSucursal')->references('idSucursal')->on('TSucursales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TClaseGrupales');
    }
};
