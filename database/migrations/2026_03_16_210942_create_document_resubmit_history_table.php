<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_resubmit_history table.
 *
 * Every resubmission writes one immutable row here, capturing a full
 * snapshot of what the document looked like BEFORE the change was made.
 * This lets the tracking page show a complete resubmission timeline:
 *   - who resubmitted
 *   - what was changed (old vs new values)
 *   - what the rejection reason was that prompted the resubmission
 *   - any note the sender wrote
 *   - attempt number (1st resubmit, 2nd resubmit, …)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_resubmit_history', function (Blueprint $table) {
            $table->id();

            // The document this history row belongs to
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')
                  ->references('id')->on('documents')
                  ->cascadeOnDelete();

            // Which attempt this is (1, 2, 3 …)
            $table->unsignedInteger('attempt')->default(1);

            // ── Snapshot of values BEFORE this resubmission ──────────
            $table->string('previous_title')->nullable();
            $table->string('previous_document_type')->nullable();
            $table->unsignedBigInteger('previous_receiving_unit_id')->nullable();
            $table->string('previous_file_path')->nullable();

            // ── Values AFTER this resubmission ───────────────────────
            $table->string('new_title')->nullable();
            $table->string('new_document_type')->nullable();
            $table->unsignedBigInteger('new_receiving_unit_id')->nullable();
            $table->string('new_file_path')->nullable();

            // ── Context ──────────────────────────────────────────────
            // The rejection reason that triggered this resubmission
            $table->text('rejection_reason')->nullable();

            // Note written by the sender explaining what was changed
            $table->text('resubmit_notes')->nullable();

            // Who performed the resubmission
            $table->unsignedBigInteger('resubmitted_by')->nullable();
            $table->foreign('resubmitted_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->timestamps(); // created_at = exact moment of resubmission
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_resubmit_history');
    }
};