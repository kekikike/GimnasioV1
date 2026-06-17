<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TActividades', function (Blueprint $table) {
            $table->increments('idActividad');
            $table->string('nombreActividad', 100);
            $table->text('descripcionActividad')->nullable();
            $table->boolean('estado')->default(true);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TActividades');
    }
};
