<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('path');
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            $table->index(['feed_post_id', 'type']);
        });

        Schema::table('feed_likes', function (Blueprint $table) {
            $table->string('reaction_type', 20)->default('like')->after('likeable_id');

            $table->dropUnique(['user_id', 'likeable_type', 'likeable_id']);
            $table->unique(['user_id', 'likeable_type', 'likeable_id', 'reaction_type']);
        });
    }

    public function down(): void
    {
        Schema::table('feed_likes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'likeable_type', 'likeable_id', 'reaction_type']);
            $table->unique(['user_id', 'likeable_type', 'likeable_id']);
            $table->dropColumn('reaction_type');
        });

        Schema::dropIfExists('feed_media');
    }
};
