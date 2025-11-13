<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('world_cup_tournaments')) {
            return;
        }

        Schema::table('world_cup_tournaments', function (Blueprint $table) {
            if (!Schema::hasColumn('world_cup_tournaments', 'status')) {
                $table->string('status')->default('in_progress')->after('results');
            }

            if (!Schema::hasColumn('world_cup_tournaments', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('status');
            }
        });

        DB::table('world_cup_tournaments')
            ->whereNotNull('results')
            ->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('world_cup_tournaments')) {
            return;
        }

        Schema::table('world_cup_tournaments', function (Blueprint $table) {
            if (Schema::hasColumn('world_cup_tournaments', 'completed_at')) {
                $table->dropColumn('completed_at');
            }

            if (Schema::hasColumn('world_cup_tournaments', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

