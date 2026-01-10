<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('waba_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('message_template_id')->nullable()->constrained()->onDelete('set null');

            // Meta WhatsApp ID
            $table->string('meta_message_id')->nullable()->unique();
            $table->string('wamid')->nullable(); // WhatsApp Message ID

            // Message Info
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            $table->enum('type', ['template', 'text', 'image', 'video', 'document', 'audio', 'location', 'contacts'])->default('template');
            $table->text('content')->nullable(); // Message content
            $table->json('media')->nullable(); // Media info (URL, mime_type, etc)
            $table->json('template_data')->nullable(); // Template variables used

            // Status Tracking
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('queued');
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['tenant_id', 'campaign_id']);
            $table->index(['tenant_id', 'contact_id']);
            $table->index('status');
            $table->index('direction');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
