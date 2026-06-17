<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TPlanes', function (Blueprint $table) {
            $table->increments('idPlan');
            $table->string('nombrePlan', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('costoPlan', 10, 2);
            $table->integer('duracionDias');
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TPlanes');
    }
};
