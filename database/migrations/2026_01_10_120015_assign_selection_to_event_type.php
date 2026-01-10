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
        Schema::table('selections', function (Blueprint $table) {
            $table->unsignedInteger('event_type_id')->nullable();
            $table->foreign('event_type_id')->references('id')->on('event_types')->onDelete('cascade');
        });
        $bets = \App\Models\Bet::with(['selection'])->get();
        foreach ($bets as $bet) {
            $bet->selection()->update(['event_type_id' => $bet->getAttribute('event_type_id')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_type', function (Blueprint $table) {
            $table->dropForeign(['event_type_id']);
            $table->dropColumn('event_type_id');
        });
    }
};
