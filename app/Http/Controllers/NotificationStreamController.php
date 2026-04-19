<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    /**
     * Stream notifications to the client using Server-Sent Events
     */
    public function stream()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response('Unauthorized', 401);
        }

        $response = new StreamedResponse(function () use ($user) {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            // Keep connection open and send heartbeat
            $lastNotificationId = request()->query('last_id', 0);
            $checkInterval = 1; // Check every 1 second

            for ($i = 0; $i < 300; $i++) { // 5 minutes max connection
                // Get new notifications since last check (excluding document_moving type)
                $newNotifications = $user->unreadNotifications()
                    ->where('id', '>', $lastNotificationId)
                    ->where('data->type', '!=', 'document_moving')
                    ->latest()
                    ->get();

                if ($newNotifications->count() > 0) {
                    foreach ($newNotifications as $notification) {
                        // Send each new notification as an SSE message
                        echo "id: {$notification->id}\n";
                        echo "data: " . json_encode([
                            'id' => $notification->id,
                            'type' => $notification->data['type'] ?? 'unknown',
                            'data' => $notification->data,
                            'created_at' => $notification->created_at->toIso8601String(),
                        ]) . "\n\n";
                        
                        $lastNotificationId = $notification->id;
                    }
                    
                    ob_flush();
                    flush();
                }

                // Send heartbeat to keep connection alive
                echo ": heartbeat\n\n";
                
                ob_flush();
                flush();

                sleep($checkInterval);
            }

            // Close connection gracefully
            echo "event: close\n";
            echo "data: Connection closing\n\n";
        });

        return $response;
    }

    /**
     * Get unread notification count for initial load
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'count' => $user->unreadNotifications()
                ->where('data->type', '!=', 'document_moving')
                ->count(),
            'notifications' => $user->unreadNotifications()
                ->where('data->type', '!=', 'document_moving')
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'unknown',
                    'data' => $n->data,
                    'created_at' => $n->created_at->toIso8601String(),
                ])
        ]);
    }
}
