<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('banner_url', 1000)->nullable()->after('bio');
            $table->longText('banner_drawing')->nullable()->after('banner_url');
            $table->string('profile_accent', 7)->default('#7c5cfc')->after('banner_drawing');
            $table->string('profile_background', 7)->default('#0d0e1a')->after('profile_accent');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'banner_url',
                'banner_drawing',
                'profile_accent',
                'profile_background',
            ]);
        });
    }
};
