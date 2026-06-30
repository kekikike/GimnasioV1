<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TAsistenciasPersonal', function (Blueprint $table) {
            $table->string('estadoAsistencia', 20)->default('presente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('TAsistenciasPersonal', function (Blueprint $table) {
            $table->string('estadoAsistencia', 20)->default('Puntual')->change();
        });
    }
};
