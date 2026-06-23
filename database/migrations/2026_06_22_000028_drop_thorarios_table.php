<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('THorarios');
    }

    public function down(): void
    {
        // No recreamos la tabla porque no se usa en la app
    }
};
