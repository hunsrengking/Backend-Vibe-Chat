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
        Schema::create('group_chats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('creator_id')->constrained('guests')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('group_chat_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_chat_id')->constrained('group_chats')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['group_chat_id', 'guest_id']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('group_chat_id')->nullable()->after('guest_id')->constrained('group_chats')->onDelete('cascade');
            $table->string('media_path')->nullable()->after('content');
            $table->string('media_type')->nullable()->after('media_path'); // voice, image, video
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['group_chat_id']);
            $table->dropColumn(['group_chat_id', 'media_path', 'media_type']);
        });

        Schema::dropIfExists('group_chat_members');
        Schema::dropIfExists('group_chats');
    }
};
