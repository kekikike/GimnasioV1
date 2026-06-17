<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TSocios', function (Blueprint $table) {
            $table->increments('carnetSocio');
            $table->unsignedInteger('idUsuario')->unique();
            $table->string('codigoAcceso', 100);
            $table->string('direccion', 255)->nullable();
            $table->string('fotografiaUrl', 255)->nullable();
            $table->string('nombreContactoEmergencia', 150)->nullable();
            $table->integer('telefonoContactoEmergencia')->nullable();
            $table->text('observacionesMedicas')->nullable();
            $table->string('estadoSocio', 50);
            $table->integer('Asistencias')->default(0);
            $table->integer('Faltas')->default(0);
            $table->integer('strikes')->default(0);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idUsuario')->references('idUsuario')->on('TUsuarios');
        });

        DB::statement('ALTER TABLE TSocios AUTO_INCREMENT = 6700001;');
    }

    public function down(): void
    {
        Schema::dropIfExists('TSocios');
    }
};
