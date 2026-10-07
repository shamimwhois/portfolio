<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending|in_progress|review|completed|cancelled
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->timestamp('remind_at')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('agreement')->nullable();
            $table->longText('policy')->nullable();
            $table->longText('cancel_policy')->nullable();
            $table->longText('return_policy')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_complete')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_complete')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('update'); // update|milestone|note|reminder
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime')->nullable();
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('payment_method');
            $table->boolean('terms_accepted')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['notes', 'terms_accepted']);
        });

        Schema::dropIfExists('project_files');
        Schema::dropIfExists('project_updates');
        Schema::dropIfExists('project_features');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('client_projects');
    }
};
