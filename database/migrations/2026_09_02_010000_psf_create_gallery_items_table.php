<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PSF — "Nos réalisations" gallery (client brief §17).
 *
 * Categories are free text held in `psf_gallery_categories` (business_settings)
 * so PSF can add a new kind of project from the panel.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('psf_gallery_items')) {
            return;
        }

        Schema::create('psf_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->string('image', 191)->nullable();
            $table->string('image_alt_text', 191)->nullable();
            $table->string('image_storage_type', 30)->default('public');
            $table->string('category', 100)->nullable();
            $table->string('location', 191)->nullable();
            $table->date('completed_on')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psf_gallery_items');
    }
};
