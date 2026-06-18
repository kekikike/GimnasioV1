<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TAsistenciasPersonal', function (Blueprint $table) {
            $table->id('idAsistencia');
            $table->unsignedBigInteger('idEmpleado');
            $table->dateTime('horaEntrada');
            $table->dateTime('horaSalida')->nullable();
            
            $table->unsignedBigInteger('usuarioA');
            $table->ipAddress('ipA')->nullable();
            $table->timestamp('fechaA')->useCurrent();
            $table->timestamp('fechaM')->useCurrentOnUpdate()->nullable();

            $table->foreign('idEmpleado')->references('idEmpleado')->on('TEmpleados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TAsistenciasPersonal');
    }
};