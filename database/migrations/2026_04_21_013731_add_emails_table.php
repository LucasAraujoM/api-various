<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique()->index();
            $table->string('domain')->nullable()->index();
            $table->string('status')->nullable()->default('unknown')->index();
            $table->float('score', 2)->nullable();
            $table->boolean('mx')->nullable();
            $table->boolean('smtp')->nullable();
            $table->boolean('disposable')->nullable();
            $table->boolean('role')->nullable();
            $table->boolean('catch_all')->nullable();
            $table->float('confidence')->nullable();
            $table->integer('times_checked')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
