<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->boolean('entregado')->default(false)->after('leido');
            $table->timestamp('entregado_at')->nullable()->after('entregado');
        });

        DB::table('mensajes')->update([
            'entregado' => true,
            'entregado_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropColumn(['entregado', 'entregado_at']);
        });
    }
};

