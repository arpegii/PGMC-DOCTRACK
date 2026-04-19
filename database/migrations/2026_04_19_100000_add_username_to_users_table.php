<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the column as nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('email');
        });

        // Populate existing users with username based on their email
        DB::statement(
            "UPDATE users SET username = CONCAT(SUBSTRING_INDEX(email, '@', 1), '_', id) WHERE username IS NULL"
        );

        // Add unique constraint
        DB::statement('ALTER TABLE users ADD UNIQUE KEY users_username_unique (username)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
