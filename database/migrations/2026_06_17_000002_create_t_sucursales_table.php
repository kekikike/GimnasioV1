<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TSucursales', function (Blueprint $table) {
            $table->increments('idSucursal');
            $table->string('nombre', 100);
            $table->string('direccion', 255);
            $table->string('telefono', 20)->nullable();
            $table->boolean('estado')->default(true);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TSucursales');
    }
};
