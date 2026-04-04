<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('actor_type');
            $table->foreignId('actor_user_id')->nullable()->constrained('users');
            $table->string('actor_system_key')->nullable();
            $table->foreignUuid('actor_import_run_id')->nullable()->constrained('import_runs');
            $table->string('actor_job_key')->nullable();
            $table->uuid('actor_api_key_id')->nullable();
            $table->string('actor_ai_process_key')->nullable();

            // Action family + action type keeps the overall set controlled without over-specifying every string.
            $table->string('action_family')->nullable();
            $table->string('action_type');

            $table->string('target_type');
            $table->text('target_id'); // supports UUID and integer targets

            $table->jsonb('before_json')->nullable();
            $table->jsonb('after_json')->nullable();
            $table->jsonb('context_json')->nullable();

            $table->string('request_id')->nullable();
            $table->string('correlation_id')->nullable();
            $table->string('workflow_type')->nullable();
            $table->text('workflow_id')->nullable();

            $table->timestampTz('created_at')->useCurrent();

            $table->index(['actor_type', 'created_at']);
            $table->index(['target_type']);
            $table->index(['correlation_id']);
            $table->index(['workflow_type']);
        });

        DB::statement(
            "ALTER TABLE audit_logs
             ADD CONSTRAINT audit_logs_actor_type_check
             CHECK (actor_type IN ('user','system','import','job','api','ai_process'))"
        );

        Schema::create('state_transitions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('subject_type');
            $table->text('subject_id');

            $table->string('from_state')->nullable();
            $table->string('to_state');

            $table->string('reason_code')->nullable();
            $table->text('reason_note')->nullable();

            $table->string('actor_type');
            $table->foreignId('acted_by_user_id')->nullable()->constrained('users');
            $table->foreignUuid('actor_import_run_id')->nullable()->constrained('import_runs');
            $table->string('actor_job_key')->nullable();

            $table->string('workflow_type')->nullable();
            $table->text('workflow_id')->nullable();

            $table->timestampTz('changed_at')->useCurrent();

            $table->index(['subject_type']);
            $table->index(['actor_type']);
            $table->index(['workflow_type']);
        });

        DB::statement(
            "ALTER TABLE state_transitions
             ADD CONSTRAINT state_transitions_actor_type_check
             CHECK (actor_type IN ('user','system','import','job','api','ai_process'))"
        );
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

    public function down(): void
    {
        Schema::dropIfExists('state_transitions');
        Schema::dropIfExists('audit_logs');
    }
};

