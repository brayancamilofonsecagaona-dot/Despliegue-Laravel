<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->after('descripcion');
        });

        // Tareas ya existentes: inicio = fin, para que sigan viéndose en su día
        DB::table('tareas')
            ->whereNull('fecha_inicio')
            ->whereNotNull('fecha_limite')
            ->update(['fecha_inicio' => DB::raw('fecha_limite')]);
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn('fecha_inicio');
        });
    }
};