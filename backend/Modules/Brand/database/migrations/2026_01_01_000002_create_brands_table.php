<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Run the migrations.
    public function up(): void
    {
        Schema::create("brands", function (Blueprint $table) {
            $table->uuid("id")->primary(); // Use UUID as primary key
            $table->string("name")->unique(); // Brand name

            $table->string("slug")->unique(); // Unique slug for URL
            $table->text("description"); // Brand description

            $table->string("logo"); // URL or path to the brand logo
            $table->boolean("is_active")->default(true); // Active status of the brand

            $table->unsignedInteger('order')->default(0); // Order column for sorting brands, indexed for faster queries
            $table->index(['is_active', 'order']); // Composite index for featured status and order to optimize sorting queries
            $table->boolean('is_new')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("brands"); // Drop the brands table if it exists
    }
};
