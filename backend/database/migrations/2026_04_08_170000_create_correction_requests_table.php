<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('topic')->index();
            $table->string('name');
            $table->string('email');
            $table->string('council_name')->nullable()->index();
            $table->string('council_slug')->nullable()->index();
            $table->string('page_url')->nullable();
            $table->string('source_url')->nullable();
            $table->text('details');
            $table->string('status')->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->timestampTz('reviewed_at')->nullable()->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
