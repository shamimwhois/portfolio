<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('gateway', 40)->nullable()->after('payment_method');
            $table->string('gateway_reference', 190)->nullable()->after('gateway');
            $table->timestamp('paid_at')->nullable()->after('gateway_reference');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_reference', 'paid_at']);
        });
    }
};
