<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->string('guest_email', 190)->nullable()->after('session_id');
            $table->string('guest_phone', 40)->nullable()->after('guest_email');
        });
    }

    public function down(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropColumn(['guest_email', 'guest_phone']);
        });
    }
};
