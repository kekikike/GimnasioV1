<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TPenalizaciones', function (Blueprint $table) {
            $table->increments('idPenalizacion');
            $table->unsignedInteger('carnetSocio');
            $table->date('fecha');
            $table->boolean('estado')->default(true);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('carnetSocio')->references('carnetSocio')->on('TSocios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TPenalizaciones');
    }
};
