<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('saved_posts')) {
            Schema::create('saved_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'post_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_posts');
        Schema::dropIfExists('comments');
    }
};
