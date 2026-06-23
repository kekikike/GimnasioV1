<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TNotificaciones', function (Blueprint $table) {
            $table->increments('idNotificacion');
            $table->unsignedInteger('carnetSocio');
            $table->string('tipoNotificacion', 50);
            $table->text('mensaje');
            $table->date('fechaEnvio');
            $table->string('estado', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('carnetSocio')->references('carnetSocio')->on('TSocios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TNotificaciones');
    }
};
