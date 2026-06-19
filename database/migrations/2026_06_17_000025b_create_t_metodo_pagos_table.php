<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TMetodoPagos
        Schema::create('TMetodoPagos', function (Blueprint $table) {
            $table->increments('idMetodoPago');
            $table->string('nombreMetodoPago', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();
        });

        // TPagoSueldos
        Schema::create('TPagoSueldos', function (Blueprint $table) {
            $table->increments('idPagoSueldo');
            $table->integer('carnetEmpleado');
            $table->dateTime('fechaPago');
            $table->decimal('monto', 10, 2);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados');
        });

        // FK de TDetalleMetodoPagos → TMetodoPagos
        Schema::table('TDetalleMetodoPagos', function (Blueprint $table) {
            $table->foreign('idMetodoPagoFK')->references('idMetodoPago')->on('TMetodoPagos');
        });

        // FK de TPenalizaciones → TReservas
        Schema::table('TPenalizaciones', function (Blueprint $table) {
            $table->foreign('idReserva')->references('idReserva')->on('TReservas');
        });
    }

    public function down(): void
    {
        Schema::table('TPenalizaciones', function (Blueprint $table) {
            $table->dropForeign(['idReserva']);
        });
        Schema::table('TDetalleMetodoPagos', function (Blueprint $table) {
            $table->dropForeign(['idMetodoPagoFK']);
        });
        Schema::dropIfExists('TPagoSueldos');
        Schema::dropIfExists('TMetodoPagos');
    }
};
