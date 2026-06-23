<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TRecibos', function (Blueprint $table) {
            $table->increments('idRecibo');
            $table->unsignedInteger('idCaja');
            $table->unsignedInteger('idMembresia');
            $table->decimal('montoTotal', 10, 2);
            $table->dateTime('fechaPago');
            $table->enum('estadoRecibo', ['Emitido', 'Anulado']);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idCaja')->references('idCaja')->on('TCajas');
            $table->foreign('idMembresia')->references('idMembresia')->on('TMembresias');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TRecibos');
    }
};
