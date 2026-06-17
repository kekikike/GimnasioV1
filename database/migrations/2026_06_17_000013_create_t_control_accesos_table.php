<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TControlAccesos', function (Blueprint $table) {
            $table->increments('idControlAcceso');
            $table->unsignedInteger('carnetSocio');
            $table->unsignedInteger('idSucursal');
            $table->date('fechaAcceso');
            $table->time('horaAcceso');
            $table->boolean('bloqueo')->default(false);
            $table->string('motivoDenegacion', 255)->nullable();
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('carnetSocio')->references('carnetSocio')->on('TSocios');
            $table->foreign('idSucursal')->references('idSucursal')->on('TSucursales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TControlAccesos');
    }
};
