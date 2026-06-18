<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TMantenimientoPreventivos', function (Blueprint $table) {
            $table->increments('idMantenimiento');
            $table->unsignedInteger('idEquipo');
            $table->date('fechaProgramada');
            $table->date('fechaRealizada')->nullable();
            $table->text('descripcionMantenimiento')->nullable();
            $table->decimal('costoMantenimiento', 10, 2)->nullable();
            $table->string('tecnicoAsignado', 150)->nullable();
            $table->enum('estadoMantenimiento', ['Pendiente', 'Realizado', 'Cancelado']);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idEquipo')->references('idEquipo')->on('TEquipamientos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TMantenimientoPreventivos');
    }
};
