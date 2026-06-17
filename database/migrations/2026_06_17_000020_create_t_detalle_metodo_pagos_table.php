<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TDetalleMetodoPagos', function (Blueprint $table) {
            $table->increments('idMetodoPago');
            $table->unsignedInteger('idRecibo');
            $table->string('tipoPago', 50);
            $table->decimal('monto', 10, 2);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idRecibo')->references('idRecibo')->on('TRecibos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TDetalleMetodoPagos');
    }
};
