<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('source_files', function (Blueprint $table) {
            $table->foreignUuid('ingestion_source_id')
                ->nullable()
                ->constrained('ingestion_sources')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('source_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ingestion_source_id');
        });
    }
};
