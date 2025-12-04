<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');

            // Stripe Integration
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();

            // Invoice Details
            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('usd');

            // Status
            $table->enum('status', [
                'draft',
                'open',
                'paid',
                'uncollectible',
                'void'
            ])->default('draft');

            // Dates
            $table->timestamp('invoice_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Payment
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('payment_method')->nullable();

            // URLs
            $table->text('stripe_hosted_invoice_url')->nullable();
            $table->text('stripe_invoice_pdf')->nullable();

            // Metadata
            $table->json('line_items')->nullable(); // Invoice line items
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('stripe_invoice_id');
            $table->index('tenant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
