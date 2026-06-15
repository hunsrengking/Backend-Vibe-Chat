<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('group_chats', function (Blueprint $table) {
            $table->string('type')->default('public')->after('name'); // public, protected, private
            $table->string('passcode')->nullable()->after('type');
        });

        Schema::create('group_chat_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_chat_id')->constrained('group_chats')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->unique(['group_chat_id', 'guest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_chat_requests');

        Schema::table('group_chats', function (Blueprint $table) {
            $table->dropColumn(['type', 'passcode']);
        });
    }
};
