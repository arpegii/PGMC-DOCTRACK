<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_resubmit_history', function (Blueprint $table) {
            if (!Schema::hasColumn('document_resubmit_history', 'previous_file_name')) {
                $table->string('previous_file_name')->nullable()->after('previous_file_path');
            }
            if (!Schema::hasColumn('document_resubmit_history', 'new_file_name')) {
                $table->string('new_file_name')->nullable()->after('new_file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_resubmit_history', function (Blueprint $table) {
            if (Schema::hasColumn('document_resubmit_history', 'previous_file_name')) {
                $table->dropColumn('previous_file_name');
            }
            if (Schema::hasColumn('document_resubmit_history', 'new_file_name')) {
                $table->dropColumn('new_file_name');
            }
        });
    }
};
