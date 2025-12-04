<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Free", "Starter", "Professional", "Enterprise"
            $table->string('slug')->unique(); // e.g., "free", "starter", "professional"
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price_monthly', 10, 2)->default(0); // Monthly price
            $table->decimal('price_yearly', 10, 2)->default(0); // Yearly price (with discount)
            $table->string('currency', 3)->default('usd'); // USD, MXN, EUR

            // Stripe Integration
            $table->string('stripe_price_id_monthly')->nullable(); // Stripe Price ID for monthly
            $table->string('stripe_price_id_yearly')->nullable(); // Stripe Price ID for yearly
            $table->string('stripe_product_id')->nullable(); // Stripe Product ID

            // Trial Settings
            $table->boolean('has_trial')->default(false);
            $table->integer('trial_days')->default(0); // 0 = no trial, -1 = unlimited trial

            // Limits
            $table->integer('max_users')->default(1);
            $table->integer('max_contacts')->default(100);
            $table->integer('max_campaigns')->default(5);
            $table->integer('max_waba_accounts')->default(1);
            $table->integer('max_messages_per_month')->default(1000);
            $table->integer('max_storage_mb')->default(100);

            // Features (JSON for flexibility)
            $table->json('features')->nullable(); // ["feature1", "feature2", ...]
            $table->json('restrictions')->nullable(); // Custom restrictions

            // Status & Visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true); // Show in pricing page
            $table->boolean('is_default')->default(false); // Default plan for new signups
            $table->integer('sort_order')->default(0); // Display order

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
