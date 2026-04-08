<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_files', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('dataset_version_id')->nullable()->constrained('dataset_versions');
            $table->foreignUuid('import_run_id')->nullable()->constrained('import_runs');
            $table->foreignUuid('council_id')->nullable()->constrained('councils');

            $table->string('storage_provider')->default('minio');
            $table->string('storage_bucket');
            $table->string('storage_key');

            $table->string('sha256')->nullable();
            $table->string('content_type')->nullable();
            $table->unsignedBigInteger('byte_size')->nullable();

            $table->string('capture_method')->nullable(); // download, upload, email, etc.
            $table->text('source_url')->nullable();

            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('captured_at')->nullable();

            $table->boolean('is_raw_unmodified')->default(true);
            $table->string('visibility')->default('restricted');

            $table->timestamps();

            $table->index(['storage_provider', 'storage_bucket']);
            $table->index(['sha256']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE source_files
ADD CONSTRAINT source_files_visibility_check
CHECK (visibility IN ('public','restricted','private'))"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('source_files');
    }
};
