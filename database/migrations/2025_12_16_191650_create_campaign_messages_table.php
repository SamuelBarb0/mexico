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
        Schema::create('campaign_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');

            // WhatsApp Message Info
            $table->string('whatsapp_message_id')->nullable()->unique(); // ID from WhatsApp API
            $table->string('phone_number');
            $table->text('message_body'); // Rendered message with variables replaced

            // Status tracking
            $table->enum('status', ['PENDING', 'QUEUED', 'SENT', 'DELIVERED', 'READ', 'FAILED'])->default('PENDING');
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();

            // Timestamps for tracking
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Metadata
            $table->json('template_variables')->nullable(); // Variables used in template
            $table->integer('retry_count')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['campaign_id', 'status']);
            $table->index('whatsapp_message_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_messages');
    }
};
