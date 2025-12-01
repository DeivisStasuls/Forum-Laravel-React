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
        Schema::create('posts', function (Blueprint $table) {
            // Primary key for the post (reply)
            $table->id();

            // Foreign key linking the post to its parent thread.
            $table->foreignId('thread_id')
                  ->constrained() // Links to the 'id' column of the 'threads' table
                  ->onDelete('cascade');

            // Foreign key linking the post to its creator.
            $table->foreignId('user_id')
                  ->constrained() // Links to the 'id' column of the 'users' table
                  ->onDelete('cascade');

            // The content of the reply.
            $table->text('body');

            // Adds created_at and updated_at columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};