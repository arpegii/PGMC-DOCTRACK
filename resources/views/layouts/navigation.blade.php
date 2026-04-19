<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <!-- LEFT: Logo + Links -->
            <div class="flex items-center gap-4">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Company Logo"
                             class="h-9 w-auto object-contain drop-shadow-sm">
                        <div class="hidden lg:block leading-tight">
                            <p class="text-xs uppercase tracking-[0.14em] text-slate-500">PGMC</p>
                            <p class="text-sm font-semibold text-slate-900">Pension Services Tracking System</p>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden sm:ms-4 sm:flex sm:items-center sm:gap-1">

                    <x-nav-link
                        :href="route('incoming.index')"
                        :active="request()->routeIs('incoming.*')">
                        Incoming
                    </x-nav-link>

                    <x-nav-link
                        :href="route('received.index')"
                        :active="request()->routeIs('received.*')">
                        Received
                    </x-nav-link>

                    <x-nav-link
                        :href="route('outgoing.index')"
                        :active="request()->routeIs('outgoing.*')">
                        Outgoing
                    </x-nav-link>

                    <x-nav-link
                        :href="route('forwarded.index')"
                        :active="request()->routeIs('forwarded.*')">
                        Forwarded
                    </x-nav-link>

                    <x-nav-link
                        :href="route('rejected.index')"
                        :active="request()->routeIs('rejected.*')">
                        Rejected
                    </x-nav-link>

                    <x-nav-link
                        :href="route('history.index')"
                        :active="request()->routeIs('history.*')">
                        History
                    </x-nav-link>

                    <x-nav-link
                        :href="route('track.index')"
                        :active="request()->routeIs('track.*')">
                        Track
                    </x-nav-link>

                </div>
            </div>

            <!-- RIGHT: Notifications + Profile Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                @php
                    $visibleUnreadNotifications = auth()->user()->unreadNotifications
                        ->filter(fn($notification) => ($notification->data['type'] ?? null) !== 'document_moving')
                        ->values();
                @endphp
                
                <!-- Notification Bell Dropdown -->
                <div x-data="{ notificationOpen: false }" class="relative">
                    <button 
                        @click="notificationOpen = !notificationOpen"
                        @click.away="notificationOpen = false"
                        class="relative inline-flex items-center justify-center w-10 h-10 text-slate-600 hover:text-slate-900 rounded-xl border border-transparent hover:border-slate-200 hover:bg-white transition focus:outline-none">
                        <div class="relative">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            @if($visibleUnreadNotifications->count() > 0)
                                <span class="absolute -top-2 -right-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold text-white bg-red-600 rounded-full border-2 border-white shadow-sm">
                                    {{ $visibleUnreadNotifications->count() }}
                                </span>
                            @endif
                        </div>
                    </button>

                    <!-- Notification Dropdown -->
                    <div 
                        x-show="notificationOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden">
                        
                        <!-- Header -->
                        <div class="px-5 py-4 bg-white border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-slate-900">Notifications</h3>
                                <a href="{{ route('notifications.index') }}" class="text-sm text-blue-700 hover:text-blue-900 font-medium">
                                    View All
                                </a>
                            </div>
                        </div>

                        <!-- Notification List -->
                        <div class="max-h-96 overflow-y-auto bg-slate-50 modern-scrollbar">
                            @forelse($visibleUnreadNotifications->take(5) as $notification)
                                <div class="block px-5 py-4 bg-white border-b border-slate-100">
                                    <div class="flex items-start gap-3">
                                        <!-- Notification Icon -->
                                        @php
                                            $iconClass = 'fa-file';
                                            $iconColor = 'text-white';
                                            $bgColor = 'bg-blue-500';
                                            
                                            if(isset($notification->data['type'])) {
                                                switch($notification->data['type']) {
                                                    case 'document_sent':
                                                        $iconClass = 'fa-paper-plane';
                                                        $iconColor = 'text-white';
                                                        $bgColor = 'bg-blue-500';
                                                        break;
                                                    case 'document_received':
                                                        $iconClass = 'fa-check-circle';
                                                        $iconColor = 'text-white';
                                                        $bgColor = 'bg-green-500';
                                                        break;
                                                    case 'document_rejected':
                                                        $iconClass = 'fa-times-circle';
                                                        $iconColor = 'text-white';
                                                        $bgColor = 'bg-red-500';
                                                        break;
                                                    case 'document_forwarded':
                                                        $iconClass = 'fa-share';
                                                        $iconColor = 'text-white';
                                                        $bgColor = 'bg-purple-500';
                                                        break;
                                                    case 'document_overdue':
                                                        $iconClass = 'fa-exclamation-triangle';
                                                        $iconColor = 'text-white';
                                                        $bgColor = 'bg-red-500';
                                                        break;
                                                    case 'document_resubmitted':
                                                        $iconClass = 'fa-redo';
                                                        $iconColor = 'text-white';
                                                        $bgColor = 'bg-amber-500';
                                                        break;
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="flex-shrink-0 w-10 h-10 {{ $bgColor }} rounded-full flex items-center justify-center">
                                            <i class="fas {{ $iconClass }} {{ $iconColor }} text-sm"></i>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-900 leading-snug">
                                                {{ $notification->data['message'] ?? 'New notification' }}
                                            </p>
                                            @if(isset($notification->data['document_number']))
                                                <p class="text-xs text-slate-600 mt-1 font-medium">
                                                    {{ $notification->data['document_number'] }}
                                                </p>
                                            @endif
                                            <p class="text-xs text-slate-400 mt-1">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>

                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="flex-shrink-0 self-center">
                                            @csrf
                                            <input type="hidden" name="stay" value="1">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center w-5 h-5 text-blue-700 border border-blue-500 hover:bg-blue-50 rounded-full transition-all duration-200"
                                                title="Mark as read"
                                            >
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-12 text-center bg-white">
                                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-3">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium">No new notifications</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Footer -->
                        @if($visibleUnreadNotifications->count() > 0)
                            <div class="px-5 py-3 bg-white border-t border-slate-200">
                                <form action="{{ route('notifications.read-all') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-center text-sm text-blue-700 hover:text-blue-900 font-semibold py-1">
                                        Mark all as read
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-xl text-slate-700 bg-white border border-slate-200 hover:text-slate-900 hover:shadow-sm focus:outline-none transition">
                            @if (Auth::user()->profile_picture)
                                <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="h-8 w-8 rounded-full object-cover border border-slate-300 me-2">
                            @else
                                <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center me-2">
                                    <span class="text-slate-600 font-semibold text-sm">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            
                            <span>{{ Auth::user()->name }}</span>
                            
                            <svg class="fill-current h-4 w-4 ms-1" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                      clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 20a6 6 0 0112 0" />
                                </svg>
                                <span>Profile</span>
                            </span>
                        </x-dropdown-link>

                        <button
                            @click="window.dispatchEvent(new CustomEvent('open-logout-modal'))"
                            type="button"
                            class="block w-full text-left px-4 py-2 text-sm leading-5 text-slate-700 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 transition duration-150 ease-in-out">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9m4 8H7a2 2 0 01-2-2V6a2 2 0 012-2h6" />
                                </svg>
                                <span>Log Out</span>
                            </span>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Hamburger -->
            <div class="-me-3 flex items-center sm:hidden">
                <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-white transition border border-transparent hover:border-slate-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="sm:hidden border-t border-slate-200 bg-white/95 backdrop-blur">
        <div class="px-4 pt-3 pb-3 space-y-1.5">
            <x-responsive-nav-link :href="route('incoming.index')" :active="request()->routeIs('incoming.*')">
                Incoming
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('received.index')" :active="request()->routeIs('received.*')">
                Received
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('outgoing.index')" :active="request()->routeIs('outgoing.*')">
                Outgoing
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('forwarded.index')" :active="request()->routeIs('forwarded.*')">
                Forwarded
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('rejected.index')" :active="request()->routeIs('rejected.*')">
                Rejected
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('history.index')" :active="request()->routeIs('history.*')">
                History
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('track.index')" :active="request()->routeIs('track.*')">
                Track
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                <div class="flex items-center justify-between">
                    <span>Notifications</span>
                </div>
            </x-responsive-nav-link>
        </div>

        <!-- Mobile User Options -->
        <div class="pt-4 pb-4 border-t border-slate-200">
            <div class="px-4 flex items-center gap-3">
                @if (Auth::user()->profile_picture)
                    <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" 
                         alt="{{ Auth::user()->name }}" 
                         class="h-10 w-10 rounded-full object-cover border border-slate-300">
                @else
                    <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center">
                        <span class="text-slate-600 font-semibold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </span>
                    </div>
                @endif
                <div>
                    <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 px-4 space-y-1.5">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>

                <button
                    @click="window.dispatchEvent(new CustomEvent('open-logout-modal'))"
                    type="button"
                    class="block w-full text-left px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg border border-transparent hover:border-slate-200 focus:outline-none transition duration-150 ease-in-out">
                    Log Out
                </button>
            </div>
        </div>
    </div>
</nav>