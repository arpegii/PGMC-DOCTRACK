@extends('layouts.app')

@section('header')
<div class="page-hero">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Stay updated with your document activities</p>
    </div>
    @if($unreadCount > 0)
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary-modern inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Mark All as Read
            </button>
        </form>
    @endif
</div>
@endsection

@section('content')
<div class="py-6">
    <div
            class="w-full"
            x-data="{
                selectedNotifications: [],
                pageNotificationIds: @js($notifications->pluck('id')->values()),
                totalNotifications: {{ $notifications->total() }},
                selectAllAcrossPages: false,
                showConfirmModal: false,
                showSingleDeleteModal: false,
                pendingAction: '',
                singleDeleteAction: '',
                get hasSelection() {
                    return this.selectAllAcrossPages || this.selectedNotifications.length > 0;
                },
                get selectedCount() {
                    return this.selectAllAcrossPages ? this.totalNotifications : this.selectedNotifications.length;
                },
                get isAllSelected() {
                    return this.selectAllAcrossPages;
                },
                toggleSelectAll(event) {
                    if (event.target.checked) {
                        this.selectAllAcrossPages = true;
                        this.selectedNotifications = [...this.pageNotificationIds];
                        return;
                    }

                    this.selectAllAcrossPages = false;
                    this.selectedNotifications = [];
                },
                isNotificationSelected(id) {
                    return this.selectAllAcrossPages || this.selectedNotifications.includes(id);
                },
                toggleNotification(id, checked) {
                    if (this.selectAllAcrossPages) {
                        this.selectAllAcrossPages = false;
                        this.selectedNotifications = [...this.pageNotificationIds];
                    }

                    if (checked && !this.selectedNotifications.includes(id)) {
                        this.selectedNotifications.push(id);
                    }

                    if (!checked) {
                        this.selectedNotifications = this.selectedNotifications.filter((item) => item !== id);
                    }
                },
                openConfirmModal(action) {
                    if (!this.hasSelection) return;
                    this.pendingAction = action;
                    this.showConfirmModal = true;
                },
                closeConfirmModal() {
                    this.showConfirmModal = false;
                    this.pendingAction = '';
                },
                openSingleDeleteModal(notificationId) {
                    this.singleDeleteAction = `{{ route('notifications.destroy', ['id' => '__ID__']) }}`.replace('__ID__', notificationId);
                    this.showSingleDeleteModal = true;
                },
                closeSingleDeleteModal() {
                    this.showSingleDeleteModal = false;
                    this.singleDeleteAction = '';
                }
            }"
        >
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="panel-surface p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $totalCount }}</p>
                                <p class="text-sm text-slate-500">Total</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel-surface p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $unreadCount }}</p>
                                <p class="text-sm text-slate-500">Unread</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel-surface p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $readCount }}</p>
                                <p class="text-sm text-slate-500">Read</p>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- Bulk Select Actions -->
            @if($notifications->count() > 0)
                <div class="mb-5 panel-surface p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                :checked="isAllSelected"
                                @change="toggleSelectAll($event)"
                            >
                            <span>Select All (all pages)</span>
                        </label>

                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 mr-1" x-text="`${selectedCount} selected`"></span>
                            <button
                                type="button"
                                @click="openConfirmModal('read')"
                                :disabled="!hasSelection"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-all duration-200"
                                :class="hasSelection
                                    ? 'text-blue-700 bg-blue-50 hover:bg-blue-100 border-blue-200'
                                    : 'text-gray-400 bg-gray-50 border-gray-200 cursor-not-allowed'"
                            >
                                Mark selected as read
                            </button>
                            <button
                                type="button"
                                @click="openConfirmModal('delete')"
                                :disabled="!hasSelection"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-all duration-200"
                                :class="hasSelection
                                    ? 'text-red-700 bg-red-50 hover:bg-red-100 border-red-200'
                                    : 'text-gray-400 bg-gray-50 border-gray-200 cursor-not-allowed'"
                            >
                                Delete selected
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notifications List -->
            <div class="space-y-3">
                @forelse($notifications as $notification)
                    <div class="group panel-surface overflow-hidden hover:shadow-md transition-all duration-200 {{ is_null($notification->read_at) ? 'ring-2 ring-blue-500/20' : '' }}">
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="pt-1">
                                    <input
                                        type="checkbox"
                                        value="{{ $notification->id }}"
                                        :checked="isNotificationSelected('{{ $notification->id }}')"
                                        @change="toggleNotification('{{ $notification->id }}', $event.target.checked)"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                </div>

                                <!-- Notification Icon -->
                                @php
                                    $iconClass = 'fa-file';
                                    $iconColor = 'text-blue-600';
                                    $bgColor = 'bg-blue-100';
                                    $ringColor = 'ring-blue-500/20';
                                    
                                    if(isset($notification->data['type'])) {
                                        switch($notification->data['type']) {
                                            case 'document_sent':
                                                $iconClass = 'fa-paper-plane';
                                                $iconColor = 'text-blue-600';
                                                $bgColor = 'bg-blue-100';
                                                $ringColor = 'ring-blue-500/20';
                                                break;
                                            case 'document_received':
                                                $iconClass = 'fa-check-circle';
                                                $iconColor = 'text-green-600';
                                                $bgColor = 'bg-green-100';
                                                $ringColor = 'ring-green-500/20';
                                                break;
                                            case 'document_rejected':
                                                $iconClass = 'fa-times-circle';
                                                $iconColor = 'text-red-600';
                                                $bgColor = 'bg-red-100';
                                                $ringColor = 'ring-red-500/20';
                                                break;
                                            case 'document_forwarded':
                                                $iconClass = 'fa-share';
                                                $iconColor = 'text-purple-600';
                                                $bgColor = 'bg-purple-100';
                                                $ringColor = 'ring-purple-500/20';
                                                break;
                                            case 'document_overdue':
                                                $iconClass = 'fa-exclamation-triangle';
                                                $iconColor = 'text-red-600';
                                                $bgColor = 'bg-red-100';
                                                $ringColor = 'ring-red-500/20';
                                                break;
                                            case 'document_resubmitted':
                                                $iconClass = 'fa-redo';
                                                $iconColor = 'text-amber-600';
                                                $bgColor = 'bg-amber-100';
                                                $ringColor = 'ring-amber-500/20';
                                                break;
                                        }
                                    }
                                @endphp
                                
                                <div class="flex-shrink-0 w-14 h-14 {{ $bgColor }} rounded-2xl flex items-center justify-center ring-4 {{ $ringColor }} group-hover:scale-110 transition-transform duration-200">
                                    <i class="fas {{ $iconClass }} {{ $iconColor }} text-xl"></i>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4 mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                @php
                                                    $displayMessage = $notification->data['message'] ?? 'New notification';
                                                    
                                                    if ($notification->data['type'] === 'document_received' && isset($notification->data['received_by'])) {
                                                        $displayMessage = 'Your document was received by ' . $notification->data['received_by'];
                                                    } elseif ($notification->data['type'] === 'document_rejected' && isset($notification->data['rejected_by'])) {
                                                        $displayMessage = 'Your document was rejected by ' . $notification->data['rejected_by'];
                                                    } elseif ($notification->data['type'] === 'document_sent' && isset($notification->data['sender_name'])) {
                                                        $displayMessage = 'New document received from ' . $notification->data['sender_unit'];
                                                    } elseif ($notification->data['type'] === 'document_resubmitted' && isset($notification->data['resubmitted_by'])) {
                                                        $displayMessage = $notification->data['message'];
                                                    }
                                                @endphp
                                                
                                                <h3 class="text-base font-semibold text-gray-900">
                                                    {{ $displayMessage }}
                                                </h3>
                                                @if(is_null($notification->read_at))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold text-blue-700 bg-blue-100 rounded-full border border-blue-200">
                                                        NEW
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if(isset($notification->data['document_number']))
                                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-1">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span class="font-semibold">{{ $notification->data['document_number'] }}</span>
                                                    @if(isset($notification->data['title']))
                                                        <span class="text-gray-400">•</span>
                                                        <span>{{ $notification->data['title'] }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            @if($notification->data['type'] === 'document_sent' && isset($notification->data['sender_name']))
                                                <p class="text-xs text-gray-500 mb-1">
                                                    Sent by: <span class="font-semibold text-gray-700">{{ $notification->data['sender_name'] }}</span>
                                                </p>
                                            @endif
                                            
                                            @if($notification->data['type'] === 'document_received' && isset($notification->data['received_at']))
                                                <p class="text-xs text-gray-500 mb-1">
                                                    Received at: <span class="font-semibold text-gray-700">{{ $notification->data['received_at'] }}</span>
                                                </p>
                                            @endif
                                            
                                            @if($notification->data['type'] === 'document_rejected' && isset($notification->data['rejected_at']))
                                                <p class="text-xs text-gray-500 mb-1">
                                                    Rejected at: <span class="font-semibold text-gray-700">{{ $notification->data['rejected_at'] }}</span>
                                                </p>
                                            @endif
                                            
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-2">
                                            @if(is_null($notification->read_at))
                                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="stay" value="1">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-all duration-200">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75" />
                                                        </svg>
                                                        Mark as read
                                                    </button>
                                                </form>
                                            @endif

                                            <button
                                                type="button"
                                                @click="openSingleDeleteModal('{{ $notification->id }}')"
                                                class="inline-flex items-center justify-center w-9 h-9 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all duration-200 group/delete"
                                            >
                                                <svg class="w-4 h-4 group-hover/delete:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Rejection Reason Card -->
                                    @if(isset($notification->data['rejection_reason']) && $notification->data['type'] === 'document_rejected')
                                        <div class="mt-3 p-4 bg-gradient-to-br from-red-50 to-red-100/50 border border-red-200 rounded-xl">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-semibold text-red-900 mb-1">Rejection Reason</p>
                                                    <p class="text-sm text-red-800">{{ $notification->data['rejection_reason'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Forward Details Card -->
                                    @if($notification->data['type'] === 'document_forwarded')
                                        <div class="mt-3 p-4 bg-gradient-to-br from-purple-50 to-purple-100/50 border border-purple-200 rounded-xl">
                                            <div class="flex items-start gap-3">
                                                <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                                </svg>
                                                <div class="flex-1">
                                                    @if(isset($notification->data['from_unit']) && isset($notification->data['to_unit']))
                                                        <p class="text-sm font-semibold text-purple-900 mb-2">Document Route</p>
                                                        <div class="flex items-center gap-2 text-sm mb-2">
                                                            <span class="px-2.5 py-1 bg-white rounded-lg font-medium text-purple-900 border border-purple-200">
                                                                {{ $notification->data['from_unit'] }}
                                                            </span>
                                                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                            </svg>
                                                            <span class="px-2.5 py-1 bg-white rounded-lg font-medium text-purple-900 border border-purple-200">
                                                                {{ $notification->data['to_unit'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(isset($notification->data['forwarded_by']))
                                                        <p class="text-xs text-purple-700 mb-2">
                                                            Forwarded by: <span class="font-semibold">{{ $notification->data['forwarded_by'] }}</span>
                                                        </p>
                                                    @endif
                                                    
                                                    @if(isset($notification->data['notes']) && $notification->data['notes'])
                                                        <div class="mt-2 pt-2 border-t border-purple-200">
                                                            <p class="text-sm font-semibold text-purple-900 mb-1">Forwarding Notes</p>
                                                            <p class="text-sm text-purple-800">{{ $notification->data['notes'] }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Resubmit Details Card — amber/yellow theme --}}
                                    @if($notification->data['type'] === 'document_resubmitted')
                                        <div class="mt-3 p-4 bg-gradient-to-br from-amber-50 to-amber-100/50 border border-amber-200 rounded-xl">
                                            <div class="flex items-start gap-3">
                                                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-amber-900 mb-2">Resubmission Details</p>
                                                    <div class="space-y-1">
                                                        @if(isset($notification->data['resubmitted_by']))
                                                            <p class="text-xs text-amber-700">
                                                                Resubmitted by: <span class="font-semibold">{{ $notification->data['resubmitted_by'] }}</span>
                                                                @if(isset($notification->data['sender_unit']))
                                                                    ({{ $notification->data['sender_unit'] }})
                                                                @endif
                                                            </p>
                                                        @endif
                                                        @if(isset($notification->data['resubmit_attempt']))
                                                            <p class="text-xs text-amber-700">
                                                                Attempt: <span class="font-semibold">{{ $notification->data['resubmit_attempt'] }}</span>
                                                            </p>
                                                        @endif
                                                        @if(isset($notification->data['resubmitted_at']))
                                                            <p class="text-xs text-amber-700">
                                                                Resubmitted at: <span class="font-semibold">{{ $notification->data['resubmitted_at'] }}</span>
                                                            </p>
                                                        @endif
                                                    </div>
                                                    @if(isset($notification->data['resubmit_notes']) && $notification->data['resubmit_notes'])
                                                        <div class="mt-2 pt-2 border-t border-amber-200">
                                                            <p class="text-sm font-semibold text-amber-900 mb-1">Resubmission Notes</p>
                                                            <p class="text-sm text-amber-800">{{ $notification->data['resubmit_notes'] }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="panel-surface overflow-hidden">
                        <div class="text-center py-20">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full mb-6">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">No notifications yet</h3>
                            <p class="text-gray-500 max-w-sm mx-auto">When you receive notifications about your documents, they will appear here.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Single Delete Confirmation Modal -->
            <div
                x-cloak
                x-show="showSingleDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center px-4"
                style="display: none;"
            >
                <div class="absolute inset-0 bg-black/50" @click="closeSingleDeleteModal()"></div>

                <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Confirm Delete</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Are you sure you want to delete this notification?
                    </p>

                    <form method="POST" :action="singleDeleteAction">
                        @csrf
                        @method('DELETE')

                        <div class="flex items-center justify-end gap-2">
                            <button
                                type="button"
                                @click="closeSingleDeleteModal()"
                                class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition-all duration-200 bg-red-600 hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Confirmation Modal -->
            <div
                x-cloak
                x-show="showConfirmModal"
                class="fixed inset-0 z-50 flex items-center justify-center px-4"
                style="display: none;"
            >
                <div class="absolute inset-0 bg-black/50" @click="closeConfirmModal()"></div>

                <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="pendingAction === 'delete' ? 'Confirm Delete' : 'Confirm Mark as Read'"></h3>
                    <p class="text-sm text-gray-600 mb-6" x-text="pendingAction === 'delete'
                        ? `Are you sure you want to delete ${selectedCount} selected notification(s)?`
                        : `Are you sure you want to mark ${selectedCount} selected notification(s) as read?`">
                    </p>

                    <form method="POST" :action="pendingAction === 'delete' ? '{{ route('notifications.destroy-selected') }}' : '{{ route('notifications.read-selected') }}'">
                        @csrf

                        <template x-if="selectAllAcrossPages">
                            <input type="hidden" name="apply_to_all" value="1">
                        </template>

                        <template x-if="!selectAllAcrossPages">
                            <template x-for="notificationId in selectedNotifications" :key="notificationId">
                                <input type="hidden" name="notification_ids[]" :value="notificationId">
                            </template>
                        </template>

                        <div class="flex items-center justify-end gap-2">
                            <button
                                type="button"
                                @click="closeConfirmModal()"
                                class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition-all duration-200"
                                :class="pendingAction === 'delete' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                            >
                                <span x-text="pendingAction === 'delete' ? 'Delete' : 'Mark as Read'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @endif
    </div>
</div>
@endsection