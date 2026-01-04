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
        Schema::create('disciplines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('event_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('selections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('competitors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('away_id');
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->dateTime('event_date');
            $table->unsignedInteger('discipline_id');
            $table->unsignedInteger('event_type_id');
            $table->unsignedInteger('selection_id');
            $table->decimal('odds', 4);
            $table->decimal('stake');
            $table->timestamps();

            $table->foreign('discipline_id')->references('id')->on('disciplines')->onDelete('SET NULL');
            $table->foreign('home_id')->references('id')->on('competitors')->onDelete('SET NULL');
            $table->foreign('away_id')->references('id')->on('competitors')->onDelete('SET NULL');
            $table->foreign('event_type_id')->references('id')->on('event_types')->onDelete('SET NULL');
            $table->foreign('selection_id')->references('id')->on('selections')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_types');
        Schema::dropIfExists('selections');
        Schema::dropIfExists('competitors');
        Schema::dropIfExists('bets');
        Schema::dropIfExists('disciplines');
    }
};
