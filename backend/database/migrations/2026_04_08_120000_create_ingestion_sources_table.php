<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingestion_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('dataset_id')->nullable()->constrained('datasets');
            $table->foreignUuid('import_id')->nullable()->constrained('imports');
            $table->foreignUuid('council_id')->nullable()->constrained('councils');

            $table->string('source_key')->unique();
            $table->string('source_kind');
            $table->string('source_name')->nullable();

            $table->text('source_url')->nullable();
            $table->text('discovery_url')->nullable();

            $table->string('adapter_key')->nullable();
            $table->string('refresh_mode')->default('manual');
            $table->string('expected_refresh_cadence')->nullable();

            $table->timestampTz('last_checked_at')->nullable();
            $table->timestampTz('last_success_at')->nullable();
            $table->timestampTz('last_failure_at')->nullable();
            $table->text('last_error_summary')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['dataset_id', 'source_kind']);
            $table->index(['council_id', 'source_kind']);
            $table->index(['import_id', 'is_active']);
            $table->index(['refresh_mode']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE ingestion_sources
ADD CONSTRAINT ingestion_sources_source_kind_check
CHECK (source_kind IN ('api','csv','html','xlsx','pdf','document','other'))"
            );
            DB::statement(
                "ALTER TABLE ingestion_sources
ADD CONSTRAINT ingestion_sources_refresh_mode_check
CHECK (refresh_mode IN ('manual','scheduled','automatic'))"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ingestion_sources');
    }
};
