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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('waba_account_id')->nullable()->constrained()->onDelete('set null');

            // Template info
            $table->string('name');
            $table->string('language', 10)->default('es'); // es, en, pt, etc.
            $table->enum('category', ['MARKETING', 'UTILITY', 'AUTHENTICATION'])->default('MARKETING');
            $table->enum('status', ['DRAFT', 'PENDING', 'APPROVED', 'REJECTED', 'DISABLED'])->default('DRAFT');

            // Meta info
            $table->string('meta_template_id')->nullable()->unique();
            $table->string('meta_status')->nullable(); // status from Meta

            // Template structure (JSON)
            $table->json('components'); // header, body, footer, buttons
            $table->json('variables')->nullable(); // variable placeholders info

            // Rejection/Quality info
            $table->text('rejection_reason')->nullable();
            $table->enum('quality_score', ['GREEN', 'YELLOW', 'RED', 'UNKNOWN'])->default('UNKNOWN');

            // Metadata
            $table->text('description')->nullable();
            $table->json('tags')->nullable(); // for categorization
            $table->integer('usage_count')->default(0); // track how many times used

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category']);
            $table->unique(['tenant_id', 'name', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
