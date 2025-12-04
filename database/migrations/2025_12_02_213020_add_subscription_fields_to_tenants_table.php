<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('slug');
            $table->string('billing_email')->nullable()->after('stripe_customer_id');
            $table->string('billing_name')->nullable()->after('billing_email');
            $table->json('billing_address')->nullable()->after('billing_name');
            $table->string('tax_id')->nullable()->after('billing_address'); // VAT, RFC, etc.
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'billing_email',
                'billing_name',
                'billing_address',
                'tax_id'
            ]);
        });
    }
};
