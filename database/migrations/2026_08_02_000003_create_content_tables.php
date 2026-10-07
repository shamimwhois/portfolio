<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_packages', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('javascript')->index(); // javascript | php | python | managers
            $table->string('icon', 8)->nullable();
            $table->string('title');
            $table->string('version')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('command')->nullable();
            $table->json('features')->nullable();
            $table->json('install_commands')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('docs_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->json('tags')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('timeline_phases', function (Blueprint $table) {
            $table->id();
            $table->string('days');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_phases');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('tech_packages');
    }
};
