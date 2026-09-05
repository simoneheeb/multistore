<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Brand\Models\Brand;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('brand_id')->constrained('brands')->cascadeOnDelete();

            $table->uuid('parent_id')->nullable();
            // self reference
            $table->foreign('parent_id')->references('id')->on('categories')->cascadeOnDelete();

            $table->string('name');

            $table->string('slug');

            $table->text('description')->nullable();

            $table->string('logo')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_new')->default(true);

            $table->unsignedInteger('order')->default(0);

            $table->timestamps();


            $table->unique(['brand_id', 'slug']);

            $table->index(['brand_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
