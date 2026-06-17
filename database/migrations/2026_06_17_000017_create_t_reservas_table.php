<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TReservas', function (Blueprint $table) {
            $table->increments('idReserva');
            $table->unsignedInteger('idClaseGrupal');
            $table->unsignedInteger('carnetSocio');
            $table->date('fechaReserva');
            $table->time('horaReserva');
            $table->string('estadoReserva', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idClaseGrupal')->references('idClaseGrupal')->on('TClaseGrupales');
            $table->foreign('carnetSocio')->references('carnetSocio')->on('TSocios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TReservas');
    }
};
