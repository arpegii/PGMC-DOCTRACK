/**
 * Real-time Notification System using Server-Sent Events (SSE)
 * Provides instant notification updates without page refresh
 */

export function initializeNotificationStream() {
    // Alpine.js component data
    return {
        notifications: [],
        unreadCount: 0,
        notificationOpen: false,
        eventSource: null,
        reconnectAttempts: 0,
        maxReconnectAttempts: 5,
        reconnectDelay: 3000,

        init() {
            // Load initial unread count
            this.loadInitialNotifications();
            // Start the SSE connection
            this.connectStream();
        },

        loadInitialNotifications() {
            fetch("/api/notifications/unread-count")
                .then((response) => response.json())
                .then((data) => {
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.count || 0;
                })
                .catch((error) => {
                    console.error(
                        "Failed to load initial notifications:",
                        error,
                    );
                });
        },

        connectStream() {
            // Close existing connection if any
            if (this.eventSource) {
                this.eventSource.close();
            }

            // Get the last notification ID to avoid duplicates
            const lastId =
                this.notifications.length > 0 ? this.notifications[0].id : 0;

            // Create SSE connection
            this.eventSource = new EventSource(
                `/notifications/stream?last_id=${lastId}`,
            );

            // Handle incoming notifications
            this.eventSource.addEventListener("message", (event) => {
                try {
                    const data = JSON.parse(event.data);

                    // Add new notification to the list
                    this.addNotification(data);

                    // Increment unread count
                    this.unreadCount = Math.max(this.unreadCount + 1, 1);

                    // Reset reconnect attempts on successful message
                    this.reconnectAttempts = 0;

                    // Show browser notification (if permitted)
                    this.showBrowserNotification(data);
                } catch (error) {
                    console.error("Failed to parse notification:", error);
                }
            });

            // Handle connection close
            this.eventSource.addEventListener("close", () => {
                this.handleDisconnect();
            });

            // Handle errors
            this.eventSource.onerror = () => {
                this.handleDisconnect();
            };
        },

        addNotification(notification) {
            // Check if notification already exists
            const exists = this.notifications.some(
                (n) => n.id === notification.id,
            );
            if (exists) return;

            // Add to the beginning of the list
            this.notifications.unshift(notification);

            // Keep only the last 20 notifications in memory
            if (this.notifications.length > 20) {
                this.notifications = this.notifications.slice(0, 20);
            }

            // Play notification sound
            this.playNotificationSound();
        },

        showBrowserNotification(notification) {
            // Check if browser notifications are supported and permitted
            if (
                "Notification" in window &&
                Notification.permission === "granted"
            ) {
                const typeMap = {
                    document_sent: "📤 Document Sent",
                    document_received: "✅ Document Received",
                    document_rejected: "❌ Document Rejected",
                    document_forwarded: "↗️ Document Forwarded",
                    document_overdue: "⏰ Document Overdue",
                    document_resubmitted: "🔄 Document Resubmitted",
                };

                const title = typeMap[notification.type] || "New Notification";
                const options = {
                    icon: "/images/logo.png",
                    body:
                        notification.data.message ||
                        "You have a new notification",
                    tag: `notification-${notification.id}`,
                    requireInteraction: true,
                };

                try {
                    new Notification(title, options);
                } catch (error) {
                    console.error(
                        "Failed to show browser notification:",
                        error,
                    );
                }
            }
        },

        playNotificationSound() {
            // Play a subtle notification sound
            const audioContext =
                window.AudioContext || window.webkitAudioContext;
            if (!audioContext) return;

            try {
                const context = new audioContext();
                const oscillator = context.createOscillator();
                const gain = context.createGain();

                oscillator.connect(gain);
                gain.connect(context.destination);

                oscillator.frequency.value = 1000;
                oscillator.type = "sine";

                gain.gain.setValueAtTime(0.3, context.currentTime);
                gain.gain.exponentialRampToValueAtTime(
                    0.01,
                    context.currentTime + 0.5,
                );

                oscillator.start(context.currentTime);
                oscillator.stop(context.currentTime + 0.5);
            } catch (error) {
                // Silently fail if audio context not available
            }
        },

        handleDisconnect() {
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
            }

            // Attempt to reconnect
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                const delay =
                    this.reconnectDelay *
                    Math.pow(2, this.reconnectAttempts - 1);
                console.log(
                    `Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts})`,
                );

                setTimeout(() => {
                    this.connectStream();
                }, delay);
            } else {
                console.error("Max reconnection attempts reached");
                // Fallback to polling every 30 seconds
                this.startPollingFallback();
            }
        },

        startPollingFallback() {
            // If SSE fails, fall back to polling every 30 seconds
            setInterval(() => {
                this.loadInitialNotifications();
            }, 30000);
        },

        markAsRead(notificationId) {
            // Find and remove the notification from the list
            this.notifications = this.notifications.filter(
                (n) => n.id !== notificationId,
            );

            // Update unread count
            this.unreadCount = Math.max(this.unreadCount - 1, 0);

            // Send read request to server
            fetch(`/notifications/${notificationId}/read`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    "Content-Type": "application/json",
                },
            }).catch((error) =>
                console.error("Failed to mark as read:", error),
            );
        },

        markAllAsRead() {
            // Clear all notifications
            this.notifications = [];
            this.unreadCount = 0;

            // Send read-all request to server
            fetch("/notifications/read-all", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    "Content-Type": "application/json",
                },
            }).catch((error) =>
                console.error("Failed to mark all as read:", error),
            );
        },

        getNotificationIcon(type) {
            const iconMap = {
                document_sent: {
                    class: "fa-paper-plane",
                    color: "bg-blue-500",
                },
                document_received: {
                    class: "fa-check-circle",
                    color: "bg-green-500",
                },
                document_rejected: {
                    class: "fa-times-circle",
                    color: "bg-red-500",
                },
                document_forwarded: {
                    class: "fa-share",
                    color: "bg-purple-500",
                },
                document_overdue: {
                    class: "fa-exclamation-triangle",
                    color: "bg-red-500",
                },
                document_resubmitted: {
                    class: "fa-redo",
                    color: "bg-amber-500",
                },
            };
            return iconMap[type] || { class: "fa-file", color: "bg-blue-500" };
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // difference in seconds

            if (diff < 60) return "just now";
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
            if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;

            return date.toLocaleDateString();
        },

        destroy() {
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
            }
        },
    };
}

// Request browser notification permission on page load
export function requestNotificationPermission() {
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission().catch((error) => {
            console.error("Failed to request notification permission:", error);
        });
    }
}
