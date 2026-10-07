<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feed media gains a `document` type (PDFs, office files, archives) in
     * addition to `image` / `video`. The original enum is turned into a plain
     * string so new types can be added without further migrations, and a
     * `filename` column stores the user's original name for display + the
     * Content-Disposition header.
     */
    public function up(): void
    {
        Schema::table('feed_media', function (Blueprint $table) {
            $table->string('type', 20)->default('image')->change();
            $table->string('filename')->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('feed_media', function (Blueprint $table) {
            $table->dropColumn('filename');
            $table->enum('type', ['image', 'video'])->default('image')->change();
        });
    }
};
