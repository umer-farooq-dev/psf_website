<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PSF — catalogue fields the client brief needs.
 *
 *  products.availability      En stock / Sur commande  (brief §7)
 *  products.short_description short text for product cards
 *  products.image_alt_text    SEO + accessibility (brief §19)
 *  categories.description     category page content (brief §6)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'availability')) {
                // in_stock | on_order
                $table->string('availability', 20)->default('in_stock')->after('current_stock');
            }
            if (!Schema::hasColumn('products', 'short_description')) {
                $table->string('short_description', 500)->nullable()->after('details');
            }
            if (!Schema::hasColumn('products', 'image_alt_text')) {
                $table->string('image_alt_text', 255)->nullable()->after('thumbnail');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['availability', 'short_description', 'image_alt_text'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
