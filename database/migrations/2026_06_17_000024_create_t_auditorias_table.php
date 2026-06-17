<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TAuditorias', function (Blueprint $table) {
            $table->increments('idAuditoria');
            $table->string('tablaNombre', 50)->nullable();
            $table->integer('registroId')->nullable();
            $table->string('accion', 50)->nullable();
            $table->string('campo', 100)->nullable();
            $table->text('valorAnterior')->nullable();
            $table->text('valorNuevo')->nullable();
            $table->unsignedInteger('usuarioA')->nullable();
            $table->dateTime('fechaA')->nullable()->useCurrent();
            $table->string('direccionIP', 50)->nullable();
            $table->string('detalles', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TAuditorias');
    }
};
