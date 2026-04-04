<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('canonical_name');
            $table->string('organisation_kind')->nullable();
            $table->string('jurisdiction_code')->nullable();
            $table->timestamps();

            $table->index(['canonical_name']);
        });

        Schema::create('organisation_identifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')->constrained('organisations');

            $table->string('identifier_scheme');
            $table->string('identifier_value');

            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            $table->string('mapping_confidence')->default('unknown');

            // Phase 1: provenance via source_files only (no generic link table yet).
            $table->foreignUuid('source_file_id')->nullable()->constrained('source_files');

            $table->timestamps();

            $table->index(['identifier_scheme', 'identifier_value']);
        });

        Schema::create('organisation_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')->constrained('organisations');

            $table->string('alias');
            $table->string('alias_type')->default('observed');

            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            $table->string('mapping_confidence')->default('unknown');

            $table->foreignUuid('dataset_version_id')->nullable()->constrained('dataset_versions');
            $table->foreignUuid('source_file_id')->nullable()->constrained('source_files');

            $table->timestamps();

            $table->index(['alias']);
        });

        DB::statement(
            "ALTER TABLE organisation_identifiers
             ADD CONSTRAINT organisation_identifiers_mapping_confidence_check
             CHECK (mapping_confidence IN ('high','medium','low','unknown'))"
        );
        DB::statement(
            "ALTER TABLE organisation_aliases
             ADD CONSTRAINT organisation_aliases_mapping_confidence_check
             CHECK (mapping_confidence IN ('high','medium','low','unknown'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_aliases');
        Schema::dropIfExists('organisation_identifiers');
        Schema::dropIfExists('organisations');
    }
};

