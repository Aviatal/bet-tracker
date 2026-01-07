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
        $bets = \App\Models\Bet::all();
        Schema::create('slips', function (Blueprint $table) {
            $table->id();
            $table->decimal('odds', 4);
            $table->decimal('stake');
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->timestamps();
        });
        Schema::table('bets', function (Blueprint $table) {
            $table->dropColumn('odds');
            $table->dropColumn('stake');
            $table->dropColumn('status');

            $table->unsignedBigInteger('slip_id')->nullable();
            $table->foreign('slip_id')->references('id')->on('slips');
        });
        foreach ($bets as $bet) {
            $slip = \App\Models\Slip::query()->create([
                'odds' => $bet->odds,
                'stake' => $bet->stake,
                'status' => $bet->status,
            ]);
            $bet->update(['slip_id' => $slip->getAttribute('slip_id')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::table('bets', function (Blueprint $table) {
            $table->decimal('odds', 4);
            $table->decimal('stake');
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->dropForeign(['slip_id']);
            $table->dropColumn('slip_id');
        });
    }
};
