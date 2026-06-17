<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TReporteFallas', function (Blueprint $table) {
            $table->increments('idReporteFalla');
            $table->unsignedInteger('idEquipo');
            $table->integer('carnetEmpleado');
            $table->date('fechaReporte');
            $table->time('horaReporte');
            $table->text('descripcionFalla');
            $table->string('gravedad', 50);
            $table->string('estadoReporte', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idEquipo')->references('idEquipo')->on('TEquipamientos');
            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TReporteFallas');
    }
};
