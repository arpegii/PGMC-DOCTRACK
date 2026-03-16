<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailChangeController;
use App\Http\Controllers\IncomingController;
use App\Http\Controllers\ReceivedController;
use App\Http\Controllers\OutgoingController;
use App\Http\Controllers\RejectedController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard redirect to incoming
Route::get('/dashboard', function () {
    return redirect()->route('incoming.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public route for email change verification (doesn't require auth since user might be logged out)
Route::get('/email-change/verify', [EmailChangeController::class, 'verify'])
    ->name('email-change.verify');

// Authenticated pages
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Email change verification route
    Route::post('/email-change/send-verification', [EmailChangeController::class, 'sendVerification'])
        ->name('email-change.send-verification');

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/selected/read', [NotificationController::class, 'markSelectedAsRead'])->name('notifications.read-selected');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/selected/delete', [NotificationController::class, 'destroySelected'])->name('notifications.destroy-selected');

    /*
    |--------------------------------------------------------------------------
    | Document Pages (Your Existing Controllers)
    |--------------------------------------------------------------------------
    */
    Route::get('/incoming', [IncomingController::class, 'index'])->name('incoming.index');
    Route::get('/received', [ReceivedController::class, 'index'])->name('received.index');
    Route::get('/outgoing', [OutgoingController::class, 'index'])->name('outgoing.index');
    Route::get('/rejected', [RejectedController::class, 'index'])->name('rejected.index');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/track', [TrackController::class, 'index'])->name('track.index');

    /*
    |--------------------------------------------------------------------------
    | Document Management Routes
    |--------------------------------------------------------------------------
    */

    Route::post('/documents/{id}/forward', [DocumentController::class, 'forward'])->name('documents.forward');

    // Get next document number (AJAX) - for auto-increment
    Route::get('/documents/next-number', [DocumentController::class, 'getNextDocumentNumber'])
        ->name('documents.next-number');

    // Show forwarded documents
    Route::get('/forwarded', [DocumentController::class, 'forwarded'])->name('forwarded.index');

    // Document CRUD operations
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{id}/view', [DocumentController::class, 'view'])->name('documents.view');
    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{id}/receive', [DocumentController::class, 'receive'])->name('documents.receive');
    Route::post('/documents/{id}/reject', [DocumentController::class, 'reject'])->name('documents.reject');

    // Resubmit a rejected document
    Route::patch('/documents/{id}/resubmit', [RejectedController::class, 'resubmit'])->name('documents.resubmit');

    /*
    |--------------------------------------------------------------------------
    | Report Generation Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

});

require __DIR__.'/auth.php';