<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('handle')->nullable()->unique()->after('id');
            $table->string('display_name')->nullable()->after('name');
            $table->text('public_bio')->nullable()->after('email_verified_at');
            $table->string('account_state')->default('active')->after('public_bio');
            $table->string('verification_level')->default('unverified')->after('account_state');
            $table->string('trust_level')->default('untrusted')->after('verification_level');
            $table->boolean('is_super_admin')->default(false)->after('trust_level');
            $table->string('two_factor_mode')->default('email_code')->after('is_super_admin');
            $table->timestamp('last_seen_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['handle']);
            $table->dropColumn([
                'handle',
                'display_name',
                'public_bio',
                'account_state',
                'verification_level',
                'trust_level',
                'is_super_admin',
                'two_factor_mode',
                'last_seen_at',
            ]);
        });
    }
};
