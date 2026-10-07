<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a 3-state publish status (draft | published | unpublished) to every
     * public content type, plus a validity label to tech packages so the
     * service-list marketplace can show "30 Days" style plans.
     *
     * Named publish_status (not `status`) because `projects.status` is already
     * the lifecycle label (Live / Completed / ...).
     */
    public function up(): void
    {
        foreach (['feed_posts', 'projects', 'faqs', 'tech_packages', 'service_categories', 'service_tiers', 'skills', 'timeline_phases'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('publish_status', 20)->default('published')->index()->after('id');
            });
        }

        Schema::table('tech_packages', function (Blueprint $table) {
            $table->string('validity', 40)->default('30 Days')->nullable()->after('billing');
        });
    }

    public function down(): void
    {
        foreach (['feed_posts', 'projects', 'faqs', 'tech_packages', 'service_categories', 'service_tiers', 'skills', 'timeline_phases'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('publish_status');
            });
        }

        Schema::table('tech_packages', function (Blueprint $table) {
            $table->dropColumn('validity');
        });
    }
};
