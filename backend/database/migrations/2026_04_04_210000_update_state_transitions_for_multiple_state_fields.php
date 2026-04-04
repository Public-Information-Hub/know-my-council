<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('state_transitions', function (Blueprint $table) {
            // Rename "subject" -> "target" to align with audit_logs naming.
            $table->renameColumn('subject_type', 'target_type');
            $table->renameColumn('subject_id', 'target_id');
        });

        Schema::table('state_transitions', function (Blueprint $table) {
            // Which state field changed on the target (e.g. public_state, run_state).
            $table->string('state_field')->default('public_state')->after('target_id');

            $table->index(['target_type', 'target_id', 'state_field'], 'state_transitions_target_state_field_idx');
            $table->index(['state_field'], 'state_transitions_state_field_idx');
        });

        // Drop the original "publication-only" constraints.
        DB::statement('ALTER TABLE state_transitions DROP CONSTRAINT IF EXISTS state_transitions_to_state_check');
        DB::statement('ALTER TABLE state_transitions DROP CONSTRAINT IF EXISTS state_transitions_from_state_check');

        // Phase 1: controlled vocabulary for which state fields are allowed to be tracked.
        DB::statement(
            "ALTER TABLE state_transitions
             ADD CONSTRAINT state_transitions_state_field_check
             CHECK (state_field IN ('public_state','run_state'))"
        );

        // Enforce the correct vocabulary based on state_field without locking state_transitions to one set forever.
        DB::statement(
            "ALTER TABLE state_transitions
             ADD CONSTRAINT state_transitions_to_state_by_field_check
             CHECK (
                (state_field = 'public_state' AND to_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))
                OR
                (state_field = 'run_state' AND to_state IN ('queued','running','succeeded','failed','cancelled'))
             )"
        );
        DB::statement(
            "ALTER TABLE state_transitions
             ADD CONSTRAINT state_transitions_from_state_by_field_check
             CHECK (
                from_state IS NULL
                OR (state_field = 'public_state' AND from_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))
                OR (state_field = 'run_state' AND from_state IN ('queued','running','succeeded','failed','cancelled'))
             )"
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE state_transitions DROP CONSTRAINT IF EXISTS state_transitions_from_state_by_field_check');
        DB::statement('ALTER TABLE state_transitions DROP CONSTRAINT IF EXISTS state_transitions_to_state_by_field_check');
        DB::statement('ALTER TABLE state_transitions DROP CONSTRAINT IF EXISTS state_transitions_state_field_check');

        Schema::table('state_transitions', function (Blueprint $table) {
            $table->dropIndex('state_transitions_target_state_field_idx');
            $table->dropIndex('state_transitions_state_field_idx');
            $table->dropColumn('state_field');

            $table->renameColumn('target_type', 'subject_type');
            $table->renameColumn('target_id', 'subject_id');
        });

        // Restore the original "publication-only" constraints.
        DB::statement(
            "ALTER TABLE state_transitions
             ADD CONSTRAINT state_transitions_to_state_check
             CHECK (to_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))"
        );
        DB::statement(
            "ALTER TABLE state_transitions
             ADD CONSTRAINT state_transitions_from_state_check
             CHECK (from_state IS NULL OR from_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))"
        );
    }
};

