<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bulk_jobs', function (Blueprint $table) {
            $table->dropColumn('file_path');
            $table->longText('results')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_jobs', function (Blueprint $table) {
            $table->dropColumn('results');
            $table->string('file_path');
        });
    }
};