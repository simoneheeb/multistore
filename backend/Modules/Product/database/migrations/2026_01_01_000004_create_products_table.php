<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Brand\Models\Brand;
use Modules\Category\Models\Category;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign keys
            $table->foreignUuid('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();

            // Basic info
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Product identifiers
            $table->string('pid')->unique()->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_new')->default(false);

            // Media & Attributes
            $table->string('featured_img');
            $table->json('gallery')->nullable();
            $table->json('attributes')->nullable();

            $table->timestamps();

            // Indexes. Note the second argument of index() is the index
            // *name*, not a second column - the original composite index was
            // silently indexing brand_id alone.
            $table->index(['is_active', 'is_new']);
            $table->index(['brand_id', 'category_id']);
            $table->index(['category_id', 'is_active']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};