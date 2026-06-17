<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TEquipamientos', function (Blueprint $table) {
            $table->increments('idEquipo');
            $table->unsignedInteger('idSucursal');
            $table->unsignedInteger('idMarca');
            $table->string('nombreEquipo', 100);
            $table->string('modelo', 100)->nullable();
            $table->date('fechaAdquisicion')->nullable();
            $table->string('estadoEquipo', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idSucursal')->references('idSucursal')->on('TSucursales');
            $table->foreign('idMarca')->references('idMarca')->on('TMarcas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TEquipamientos');
    }
};
