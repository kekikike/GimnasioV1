<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TEmpleados', function (Blueprint $table) {
            $table->integer('carnetEmpleado')->primary();
            $table->unsignedInteger('idUsuario')->unique();
            $table->unsignedInteger('idSucursal');
            $table->decimal('sueldo', 10, 2);
            $table->integer('especialidad');
            $table->date('fechaContratoInicio');
            $table->date('fechaContratoFin')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idUsuario')->references('idUsuario')->on('TUsuarios');
            $table->foreign('idSucursal')->references('idSucursal')->on('TSucursales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TEmpleados');
    }
};
