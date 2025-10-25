<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->string('descripcion')->nullable()->after('nombre');
            $table->foreignId('creador_id')->nullable()->constrained('registros')->onDelete('set null')->after('descripcion');
            $table->string('imagen_grupo')->nullable()->after('creador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'creador_id', 'imagen_grupo']);
        });
    }
};
