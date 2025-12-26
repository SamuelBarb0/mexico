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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('message_template_id')->nullable()->after('waba_account_id')->constrained()->onDelete('set null');
            $table->json('template_variables_mapping')->nullable()->after('message_template');
            $table->integer('response_count')->default(0)->after('failed_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['message_template_id']);
            $table->dropColumn(['message_template_id', 'template_variables_mapping', 'response_count']);
        });
    }
};
