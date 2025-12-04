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
        Schema::create('tenant_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->integer('max_users')->default(10);
            $table->integer('max_contacts')->default(1000);
            $table->integer('max_campaigns')->default(50);
            $table->integer('max_waba_accounts')->default(1);
            $table->integer('max_messages_per_month')->default(10000);
            $table->integer('max_storage_mb')->default(1024);
            $table->integer('current_users')->default(0);
            $table->integer('current_contacts')->default(0);
            $table->integer('current_campaigns')->default(0);
            $table->integer('current_waba_accounts')->default(0);
            $table->integer('current_messages_this_month')->default(0);
            $table->integer('current_storage_mb')->default(0);
            $table->timestamps();

            $table->unique('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_limits');
    }
};
