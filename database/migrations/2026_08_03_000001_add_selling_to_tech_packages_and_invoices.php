<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tech_packages', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('description');
            $table->string('billing', 20)->default('one-time')->after('price');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('tech_package_id')->nullable()->after('service_tier_id')->constrained('tech_packages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tech_package_id');
        });

        Schema::table('tech_packages', function (Blueprint $table) {
            $table->dropColumn(['price', 'billing']);
        });
    }
};
