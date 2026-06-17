<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TRoles', function (Blueprint $table) {
            $table->increments('idRol');
            $table->string('nombreRol', 50);
            $table->boolean('estadoA')->default(true);
            $table->dateTime('fechaA')->useCurrent();
            $table->unsignedInteger('usuarioA')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TRoles');
    }
};
