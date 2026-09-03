<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PSF — "Demande de devis" (client brief §16).
 *
 * Fields the brief asks for: Nom, Téléphone, WhatsApp, Email (optional),
 * Type de client (Particulier / Plombier / Entreprise), Produit recherché,
 * Quantité, Message, and an optional photo/document.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('psf_quote_requests')) {
            return;
        }

        Schema::create('psf_quote_requests', function (Blueprint $table) {
            $table->id();

            $table->string('name', 191);
            $table->string('phone', 40);
            $table->string('whatsapp', 40)->nullable();
            $table->string('email', 191)->nullable();

            // key from the admin-managed list (business_settings -> psf_client_types)
            $table->string('client_type', 60)->nullable();

            $table->string('product_sought', 500)->nullable();
            $table->string('quantity', 60)->nullable();
            $table->text('message')->nullable();

            $table->string('attachment', 255)->nullable();
            $table->string('attachment_storage_type', 10)->default('public');

            // nouveau | contacted | closed
            $table->string('status', 20)->default('nouveau');
            $table->boolean('seen')->default(false);
            $table->text('admin_note')->nullable();

            $table->string('ip', 60)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psf_quote_requests');
    }
};
