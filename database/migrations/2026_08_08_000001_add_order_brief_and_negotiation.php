<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_projects', function (Blueprint $table) {
            $table->string('source')->default('admin')->after('status'); // admin|client
            $table->timestamp('submitted_at')->nullable()->after('source');
            $table->foreignId('tech_package_id')->nullable()->after('submitted_at')->constrained('tech_packages')->nullOnDelete();
            $table->foreignId('service_tier_id')->nullable()->after('tech_package_id')->constrained('service_tiers')->nullOnDelete();
            $table->string('example_url')->nullable()->after('return_policy');
            $table->string('demo_url')->nullable()->after('example_url');
            $table->text('design_notes')->nullable()->after('demo_url');
            $table->json('color_palette')->nullable()->after('design_notes');
            $table->decimal('budget_from', 12, 2)->nullable()->after('color_palette');
            $table->decimal('budget_to', 12, 2)->nullable()->after('budget_from');
            $table->string('timeline_preference')->nullable()->after('budget_to');
            $table->date('preferred_deadline')->nullable()->after('timeline_preference');
            $table->decimal('offer_price', 12, 2)->nullable()->after('preferred_deadline');
            $table->text('offer_note')->nullable()->after('offer_price');
            $table->string('offer_status')->nullable()->after('offer_note'); // pending|accepted|declined
        });

        Schema::table('project_files', function (Blueprint $table) {
            $table->string('kind')->default('deliverable')->after('mime'); // deliverable|attachment
        });

        Schema::create('project_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // image|video
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_media');

        Schema::table('project_files', function (Blueprint $table) {
            $table->dropColumn('kind');
        });

        Schema::table('client_projects', function (Blueprint $table) {
            $table->dropForeign(['tech_package_id', 'service_tier_id']);
            $table->dropColumn([
                'source', 'submitted_at', 'tech_package_id', 'service_tier_id',
                'example_url', 'demo_url', 'design_notes',
                'color_palette', 'budget_from', 'budget_to', 'timeline_preference',
                'preferred_deadline', 'offer_price', 'offer_note', 'offer_status',
            ]);
        });
    }
};
