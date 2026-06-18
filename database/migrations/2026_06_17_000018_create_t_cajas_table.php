<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TCajas', function (Blueprint $table) {
            $table->increments('idCaja');
            $table->unsignedInteger('idSucursal');
            $table->integer('carnetEmpleado');
            $table->date('fechaApertura');
            $table->time('horaApertura');
            $table->decimal('montoApertura', 10, 2);
            $table->decimal('montoCierre', 10, 2)->nullable();
            $table->decimal('montoCierreCalculado', 10, 2)->nullable();
            $table->decimal('diferenciaArqueo', 10, 2)->nullable();
            $table->enum('estadoCaja', ['Abierta', 'Cerrada', 'Auditada']);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idSucursal')->references('idSucursal')->on('TSucursales');
            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TCajas');
    }
};
