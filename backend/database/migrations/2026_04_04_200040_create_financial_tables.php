<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('council_id')->constrained('councils');
            $table->foreignUuid('council_version_id')->constrained('council_versions');

            $table->foreignUuid('dataset_version_id')->constrained('dataset_versions');
            $table->foreignUuid('import_run_id')->constrained('import_runs');

            $table->string('title');
            $table->text('description_observed')->nullable();
            $table->string('contract_reference')->nullable();

            $table->date('award_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->decimal('total_value_amount', 18, 2)->nullable();
            $table->char('currency', 3)->default('GBP');

            $table->string('public_state')->default('published');
            $table->timestamps();

            $table->index(['council_id', 'award_date']);
            $table->index(['dataset_version_id']);
        });

        DB::statement(
            "ALTER TABLE contracts
ADD CONSTRAINT contracts_public_state_check
CHECK (public_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))"
        );

        Schema::create('contract_suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')->constrained('contracts');
            $table->foreignUuid('organisation_id')->nullable()->constrained('organisations');

            $table->string('supplier_name_observed')->nullable();
            $table->string('role_type')->nullable();
            $table->decimal('share_fraction', 9, 8)->nullable();

            $table->string('mapping_confidence')->default('unknown');
            $table->timestamps();

            $table->index(['organisation_id']);
        });

        DB::statement(
            "ALTER TABLE contract_suppliers
ADD CONSTRAINT contract_suppliers_mapping_confidence_check
CHECK (mapping_confidence IN ('high','medium','low','unknown'))"
        );

        Schema::create('spend_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('council_id')->constrained('councils');
            $table->foreignUuid('council_version_id')->constrained('council_versions');

            $table->foreignUuid('dataset_version_id')->constrained('dataset_versions');
            $table->foreignUuid('import_run_id')->constrained('import_runs');

            $table->foreignUuid('organisation_id')->nullable()->constrained('organisations');

            $table->string('supplier_name_observed')->nullable();
            $table->text('description_observed')->nullable();

            $table->date('transaction_date')->nullable();
            $table->decimal('amount', 18, 2)->nullable();
            $table->char('currency', 3)->default('GBP');

            $table->string('mapping_confidence')->default('unknown');
            $table->string('public_state')->default('published');

            $table->timestamps();

            $table->index(['council_id', 'transaction_date']);
            $table->index(['organisation_id']);
            $table->index(['dataset_version_id']);
        });

        DB::statement(
            "ALTER TABLE spend_records
ADD CONSTRAINT spend_records_mapping_confidence_check
CHECK (mapping_confidence IN ('high','medium','low','unknown'))"
        );
        DB::statement(
            "ALTER TABLE spend_records
ADD CONSTRAINT spend_records_public_state_check
CHECK (public_state IN ('draft','submitted','under_review','approved','published','disputed','rejected','archived'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('spend_records');
        Schema::dropIfExists('contract_suppliers');
        Schema::dropIfExists('contracts');
    }
};
