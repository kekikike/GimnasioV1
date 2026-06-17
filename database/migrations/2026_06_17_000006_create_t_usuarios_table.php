<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TUsuarios', function (Blueprint $table) {
            $table->increments('idUsuario');
            $table->unsignedInteger('idRol');
            $table->string('nombre1', 100);
            $table->string('nombre2', 100)->nullable();
            $table->string('apellido1', 100);
            $table->string('apellido2', 100)->nullable();
            $table->string('correo', 150)->unique();
            $table->integer('telefono');
            $table->string('contrasena', 255);
            $table->boolean('estado')->default(true);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();

            $table->foreign('idRol')->references('idRol')->on('TRoles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TUsuarios');
    }
};
