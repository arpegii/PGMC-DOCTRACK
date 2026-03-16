<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds resubmission tracking columns to the documents table.
 *
 * - resubmit_notes      : free-text note the sender writes when resubmitting,
 *                         describing what was changed. Cleared after each new rejection.
 * - resubmit_count      : incremented every time the document is resubmitted,
 *                         so the tracking page can show "resubmitted N times".
 * - last_resubmitted_at : timestamp of the most recent resubmission.
 * - last_resubmitted_by : FK to the user who performed the last resubmission.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Place these logically after rejection_reason (col 14)
            $table->text('resubmit_notes')->nullable()->after('rejection_reason');
            $table->unsignedInteger('resubmit_count')->default(0)->after('resubmit_notes');
            $table->timestamp('last_resubmitted_at')->nullable()->after('resubmit_count');
            $table->unsignedBigInteger('last_resubmitted_by')->nullable()->after('last_resubmitted_at');

            $table->foreign('last_resubmitted_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['last_resubmitted_by']);
            $table->dropColumn([
                'resubmit_notes',
                'resubmit_count',
                'last_resubmitted_at',
                'last_resubmitted_by',
            ]);
        });
    }
};