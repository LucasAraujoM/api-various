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
        Schema::create('validations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('email_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('result');
            $table->decimal('score', 5, 2);
            $table->decimal('cost', 8, 4);
            $table->string('source');

            $table->timestamps();

            // índices útiles para tu caso
            $table->index('user_id');
            $table->index('email_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
