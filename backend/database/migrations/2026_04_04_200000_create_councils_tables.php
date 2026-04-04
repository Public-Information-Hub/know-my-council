<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('councils', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('canonical_slug')->unique();

            // UK-wide: do not hard-code England-only assumptions.
            $table->string('jurisdiction_code')->nullable(); // e.g. "GB-ENG", "GB-SCT" (convention to be finalised)
            $table->char('country_code', 2)->nullable(); // ISO 3166-1 alpha-2 where possible (GB, etc.)
            $table->string('authority_kind')->nullable(); // taxonomy to be validated before enforcing

            $table->timestamps();
        });

        Schema::create('council_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('council_id')->constrained('councils');

            $table->string('display_name');
            $table->string('short_name')->nullable();
            $table->string('status')->nullable(); // e.g. active/abolished; vocabulary to be finalised

            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            // Shared publication/workflow vocabulary (see state-and-enum-inventory.md).
            $table->string('public_state')->default('published');

            $table->timestamps();

            $table->index(['council_id', 'valid_from']);
        });

        DB::statement(
            "ALTER TABLE council_versions
             ADD CONSTRAINT council_versions_public_state_check
             CHECK (public_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('council_versions');
        Schema::dropIfExists('councils');
    }
};

