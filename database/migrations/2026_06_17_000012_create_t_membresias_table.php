<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TMembresias', function (Blueprint $table) {
            $table->increments('idMembresia');
            $table->unsignedInteger('idPlan');
            $table->unsignedInteger('carnetSocio');
            $table->unsignedInteger('idSucursal');
            $table->date('fechaInicioMembresia');
            $table->date('fechaFinMembresia');
            $table->string('estadoMembresia', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idPlan')->references('idPlan')->on('TPlanes');
            $table->foreign('carnetSocio')->references('carnetSocio')->on('TSocios');
            $table->foreign('idSucursal')->references('idSucursal')->on('TSucursales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TMembresias');
    }
};
