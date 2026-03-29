<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds missing indices for improved query performance:
     * - created_by on documents table (for filtering by creator)
     * - Composite index on notifications table for common searches
     * - from_unit_id and to_unit_id on document_forward_history
     * - document_id on document_forward_history
     * - Composite index on document status + receiving_unit for common queries
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Index for filtering documents by creator
            if (!Schema::hasColumn('documents', 'created_by')) {
                $table->index('created_by');
            } else {
                // Check if index already exists
                try {
                    $table->index('created_by');
                } catch (\Exception $e) {
                    // Index might already exist, skip
                }
            }

            // Composite index for common incoming/received queries
            $table->index(['status', 'receiving_unit_id']);
            // Composite index for outgoing documents
            $table->index(['status', 'sender_unit_id']);
        });

        Schema::table('document_forward_history', function (Blueprint $table) {
            // Indices for forward history lookups
            $table->index('document_id');
            $table->index('from_unit_id');
            $table->index('to_unit_id');
            $table->index('forwarded_by_user_id');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Composite index for common notification queries
            $table->index(['notifiable_id', 'notifiable_type', 'read_at']);
            $table->index(['notifiable_id', 'created_at']);
        });

        Schema::table('document_resubmit_history', function (Blueprint $table) {
            // Index for document resubmit history lookups
            $table->index('document_id');
            $table->index('resubmitted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status', 'receiving_unit_id']);
            $table->dropIndex(['status', 'sender_unit_id']);
        });

        Schema::table('document_forward_history', function (Blueprint $table) {
            $table->dropIndex(['document_id']);
            $table->dropIndex(['from_unit_id']);
            $table->dropIndex(['to_unit_id']);
            $table->dropIndex(['forwarded_by_user_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_id', 'notifiable_type', 'read_at']);
            $table->dropIndex(['notifiable_id', 'created_at']);
        });

        Schema::table('document_resubmit_history', function (Blueprint $table) {
            $table->dropIndex(['document_id']);
            $table->dropIndex(['resubmitted_by']);
        });
    }
};
