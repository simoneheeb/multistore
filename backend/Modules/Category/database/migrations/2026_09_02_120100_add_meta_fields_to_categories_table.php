<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Per-category SEO overrides. When empty the resource falls back
            // to the category name/description.
            $table->string('meta_title')->nullable()->after('description');
            $table->string('meta_description', 500)->nullable()->after('meta_title');

            // Cached tree depth (0 = root). Maintained by the model on save
            // so the admin tree picker and breadcrumbs can indent without
            // walking parents one query at a time.
            $table->unsignedTinyInteger('depth')->default(0)->after('parent_id');

            $table->index(['parent_id', 'order']);
            $table->index('depth');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'order']);
            $table->dropIndex(['depth']);
            $table->dropColumn(['meta_title', 'meta_description', 'depth']);
        });
    }
};
