<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the admin flag to an already-migrated users table.
 *
 * The column is also declared in the create migration, so this is guarded by
 * hasColumn(): on a fresh install it is a no-op, and on an existing database
 * it backfills the column that EnsureIsAdmin depends on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
    }
};
