<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_packages', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20)->default('tool')->index(); // combo | tool
            $table->string('title');
            $table->string('icon', 20)->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('billing', 20)->default('month'); // month | one-time
            $table->string('publish_status', 20)->default('published')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('group_package_id')->nullable()->after('tech_package_id')->constrained('group_packages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_package_id');
        });

        Schema::dropIfExists('group_packages');
    }
};
