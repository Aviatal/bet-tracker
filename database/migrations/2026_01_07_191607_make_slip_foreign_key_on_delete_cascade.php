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
        Schema::table('bets', function (Blueprint $table) {
            $table->dropForeign(['slip_id']);
        });
        Schema::table('bets', function (Blueprint $table) {
            $table->foreign('slip_id')->references('id')->on('slips')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropForeign(['slip_id']);
        });
        Schema::table('bets', function (Blueprint $table) {
            $table->foreign('slip_id')->references('id')->on('slips');
        });
    }
};
