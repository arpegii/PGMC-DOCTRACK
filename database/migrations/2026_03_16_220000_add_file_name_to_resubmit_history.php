<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_resubmit_history', function (Blueprint $table) {
            $table->string('previous_file_name')->nullable()->after('previous_file_path');
            $table->string('new_file_name')->nullable()->after('new_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('document_resubmit_history', function (Blueprint $table) {
            $table->dropColumn(['previous_file_name', 'new_file_name']);
        });
    }
};
