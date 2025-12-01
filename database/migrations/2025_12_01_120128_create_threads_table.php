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
        Schema::create('threads', function (Blueprint $table) {
            // Primary key for the thread
            $table->id();

            // 🔑 Foreign key to link the thread to its Subforum
            // When a subforum is deleted, its threads are also deleted ('cascade').
            $table->foreignId('subforum_id')
                  ->constrained() // Links to the 'id' column of the 'subforums' table
                  ->onDelete('cascade');

            // Foreign key to link the thread to its creator in the 'users' table.
            // When a user is deleted, their threads are also deleted ('cascade').
            $table->foreignId('user_id')
                ->constrained() // Links to the 'id' column of the 'users' table
                ->onDelete('cascade');

            // The main title of the thread. Indexed for fast lookups.
            $table->string('title')->index();

            // A URL-friendly slug for clean URLs (e.g., '/threads/1-my-first-post').
            $table->string('slug')->unique();

            // The initial, long content of the thread.
            $table->text('body'); // Renamed from 'content' to 'body' for clarity if preferred.

            // Adds created_at and updated_at columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};