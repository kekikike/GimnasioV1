<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TMarcas', function (Blueprint $table) {
            $table->increments('idMarca');
            $table->string('nombreMarca', 100);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TMarcas');
    }
};
