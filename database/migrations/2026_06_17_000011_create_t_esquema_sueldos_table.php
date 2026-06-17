<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TEsquemaSueldos', function (Blueprint $table) {
            $table->increments('idEsquemaSueldo');
            $table->integer('carnetEmpleado');
            $table->string('modalidadPago', 50);
            $table->decimal('montoBase', 10, 2);
            $table->integer('tarifaHoraOClase');
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('carnetEmpleado')->references('carnetEmpleado')->on('TEmpleados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TEsquemaSueldos');
    }
};
