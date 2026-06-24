<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TSalidas', function (Blueprint $table) {
            $table->increments('idSalida');
            $table->unsignedInteger('idCaja');
            $table->string('descripcion', 500);
            $table->decimal('costo', 10, 2);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA');
            $table->unsignedInteger('usuarioA');
            $table->foreign('idCaja')->references('idCaja')->on('TCajas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TSalidas');
    }
};
