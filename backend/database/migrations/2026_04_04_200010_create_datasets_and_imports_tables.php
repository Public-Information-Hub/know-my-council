<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('dataset_key')->unique();

            $table->string('publisher_name')->nullable();
            $table->string('publisher_kind')->nullable();
            $table->string('dataset_family')->nullable();
            $table->string('jurisdiction_scope')->nullable();

            $table->foreignUuid('default_council_id')->nullable()->constrained('councils');

            $table->timestamps();
        });

        Schema::create('dataset_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dataset_id')->constrained('datasets');

            $table->string('version_label')->nullable();
            $table->date('edition_date')->nullable();

            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('captured_at')->nullable();

            // Phase 1: keep geography as metadata only; do not introduce geography tables unless required.
            $table->string('geography_basis_type')->nullable();
            $table->string('code_scheme')->nullable(); // external codes used by this dataset release (if any)

            // Reporting period described by the dataset (as published).
            $table->date('reporting_period_start')->nullable();
            $table->date('reporting_period_end')->nullable();

            $table->string('mapping_confidence')->default('unknown');
            $table->string('freshness_state')->default('unknown');

            $table->string('public_state')->default('published');

            $table->timestamps();

            $table->index(['dataset_id', 'edition_date']);
            $table->index(['freshness_state']);
        });

        DB::statement(
            "ALTER TABLE dataset_versions
             ADD CONSTRAINT dataset_versions_mapping_confidence_check
             CHECK (mapping_confidence IN ('high','medium','low','unknown'))"
        );
        DB::statement(
            "ALTER TABLE dataset_versions
             ADD CONSTRAINT dataset_versions_freshness_state_check
             CHECK (freshness_state IN ('current','stale','unknown'))"
        );
        DB::statement(
            "ALTER TABLE dataset_versions
             ADD CONSTRAINT dataset_versions_public_state_check
             CHECK (public_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))"
        );

        Schema::create('imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dataset_id')->constrained('datasets');

            $table->string('import_key')->unique();
            $table->string('import_type')->nullable();
            $table->string('connector_key')->nullable();
            $table->string('parser_version')->nullable();
            $table->string('normalisation_profile')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('import_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('import_id')->constrained('imports');
            $table->foreignUuid('dataset_version_id')->constrained('dataset_versions');

            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();

            $table->string('run_state')->default('queued');
            $table->string('idempotency_key')->nullable();

            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users');
            $table->uuid('parent_import_run_id')->nullable();

            $table->unsignedBigInteger('rows_seen')->nullable();
            $table->unsignedBigInteger('rows_inserted')->nullable();
            $table->unsignedBigInteger('rows_updated')->nullable();
            $table->unsignedBigInteger('warning_count')->nullable();
            $table->text('error_summary')->nullable();

            $table->timestamps();

            $table->index(['import_id', 'started_at']);
            $table->index(['dataset_version_id', 'started_at']);
            $table->index(['run_state']);
            $table->index(['idempotency_key']);
        });

        DB::statement(
            "ALTER TABLE import_runs
             ADD CONSTRAINT import_runs_run_state_check
             CHECK (run_state IN ('queued','running','succeeded','failed','cancelled'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('import_runs');
        Schema::dropIfExists('imports');
        Schema::dropIfExists('dataset_versions');
        Schema::dropIfExists('datasets');
    }
};

