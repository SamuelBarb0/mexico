<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');

            // Billing Cycle
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');

            // Stripe Integration
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_status')->nullable(); // active, canceled, incomplete, etc.

            // Status
            $table->enum('status', [
                'trial',
                'active',
                'canceled',
                'past_due',
                'unpaid',
                'incomplete',
                'incomplete_expired',
                'paused'
            ])->default('trial');

            // Trial
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            // Subscription Dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('ends_at')->nullable(); // When canceled
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('paused_at')->nullable();

            // Renewal
            $table->boolean('auto_renew')->default(true);

            // Metadata
            $table->json('metadata')->nullable(); // Additional data from Stripe

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('stripe_subscription_id');
            $table->index('stripe_customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
