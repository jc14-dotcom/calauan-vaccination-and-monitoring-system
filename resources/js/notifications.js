/**
 * Notification System
 * Handles polling, display, and interaction with notifications
 */

class NotificationSystem {
    constructor(options = {}) {
        this.pollingInterval = options.pollingInterval || 15000; // 15 seconds
        this.lastChecked = null;
        this.unreadCount = 0;
        this.isPolling = false;
        this.pollTimer = null;
        
        this.init();
    }

    /**
     * Initialize notification system
     */
    init() {
        this.createNotificationUI();
        this.attachEventListeners();
        this.startPolling();
        
        // Load initial notifications
        this.loadNotifications();
    }

    /**
     * Create notification UI elements
     */
    createNotificationUI() {
        // Check if notification bell already exists
        if (document.getElementById('notification-bell')) {
            return;
        }

        // Find navigation area to insert notification bell
        const navArea = document.querySelector('.nav-area') || document.querySelector('nav');
        
        if (!navArea) {
            console.error('Navigation area not found');
            return;
        }

        // Create notification bell HTML
        const bellHtml = `
            <div class="relative inline-block">
                <button id="notification-bell" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span id="notification-badge" class="absolute top-0 right-0 hidden px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">
                        0
                    </span>
                </button>

                <!-- Notification Dropdown -->
                <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 max-h-96 overflow-hidden flex flex-col">
                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Mga Abiso</h3>
                        <button id="mark-all-read" class="text-sm text-blue-600 hover:text-blue-800">
                            Markahan lahat
                        </button>
                    </div>
                    <div id="notification-list" class="overflow-y-auto flex-1">
                        <div class="p-4 text-center text-gray-500">
                            Naglo-load...
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert before user profile or at the end
        const profileArea = navArea.querySelector('.profile') || navArea.lastElementChild;
        profileArea.insertAdjacentHTML('beforebegin', bellHtml);
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Toggle dropdown
        const bell = document.getElementById('notification-bell');
        if (bell) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown();
            });
        }

        // Mark all as read
        const markAllBtn = document.getElementById('mark-all-read');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('notification-dropdown');
            const bell = document.getElementById('notification-bell');
            
            if (dropdown && !dropdown.contains(e.target) && !bell.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    /**
     * Toggle notification dropdown
     */
    toggleDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                this.loadNotifications();
            }
        }
    }

    /**
     * Start polling for new notifications
     */
    startPolling() {
        if (this.isPolling) {
            return;
        }

        this.isPolling = true;
        this.poll();
    }

    /**
     * Stop polling
     */
    stopPolling() {
        this.isPolling = false;
        if (this.pollTimer) {
            clearTimeout(this.pollTimer);
            this.pollTimer = null;
        }
    }

    /**
     * Poll for new notifications
     */
    async poll() {
        if (!this.isPolling) {
            return;
        }

        try {
            const response = await fetch(`/api/notifications/check?last_checked=${this.lastChecked || ''}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    this.updateUnreadCount(data.unread_count);
                    this.lastChecked = data.timestamp;

                    // Show toast for new notifications
                    if (data.has_new && data.notifications.length > 0) {
                        this.showNewNotificationToast(data.notifications[0]);
                    }
                }
            }
        } catch (error) {
            console.error('Polling error:', error);
        }

        // Schedule next poll
        this.pollTimer = setTimeout(() => this.poll(), this.pollingInterval);
    }

    /**
     * Load notifications list
     */
    async loadNotifications() {
        const list = document.getElementById('notification-list');
        if (!list) return;

        try {
            const response = await fetch('/api/notifications', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    this.renderNotifications(data.notifications);
                    this.updateUnreadCount(data.unread_count);
                }
            } else {
                list.innerHTML = '<div class="p-4 text-center text-red-500">May error sa pag-load</div>';
            }
        } catch (error) {
            console.error('Load notifications error:', error);
            list.innerHTML = '<div class="p-4 text-center text-red-500">May error sa pag-load</div>';
        }
    }

    /**
     * Render notifications in dropdown
     */
    renderNotifications(notifications) {
        const list = document.getElementById('notification-list');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = '<div class="p-4 text-center text-gray-500">Walang mga abiso</div>';
            return;
        }

        list.innerHTML = notifications.map(notification => {
            const data = notification.data;
            const isUnread = !notification.read_at;
            const icon = this.getNotificationIcon(data.icon);
            
            return `
                <div class="notification-item ${isUnread ? 'bg-blue-50' : 'bg-white'} border-b border-gray-200 p-4 hover:bg-gray-50 cursor-pointer"
                     data-notification-id="${notification.id}"
                     data-action-url="${data.action_url || '#'}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-3">
                            ${icon}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                ${data.title}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                ${data.message}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                ${this.formatTime(notification.created_at)}
                            </p>
                        </div>
                        ${isUnread ? '<div class="ml-2 w-2 h-2 bg-blue-600 rounded-full"></div>' : ''}
                    </div>
                </div>
            `;
        }).join('');

        // Attach click handlers
        list.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.notificationId;
                const url = item.dataset.actionUrl;
                this.handleNotificationClick(id, url);
            });
        });
    }

    /**
     * Handle notification click
     */
    async handleNotificationClick(id, actionUrl) {
        // Mark as read
        await this.markAsRead(id);

        // Navigate to action URL
        if (actionUrl && actionUrl !== '#') {
            window.location.href = actionUrl;
        }
    }

    /**
     * Mark notification as read
     */
    async markAsRead(id) {
        try {
            const response = await fetch(`/api/notifications/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.updateUnreadCount(data.unread_count);
                    this.loadNotifications(); // Refresh list
                }
            }
        } catch (error) {
            console.error('Mark as read error:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.updateUnreadCount(0);
                    this.loadNotifications(); // Refresh list
                }
            }
        } catch (error) {
            console.error('Mark all as read error:', error);
        }
    }

    /**
     * Update unread count badge
     */
    updateUnreadCount(count) {
        this.unreadCount = count;
        const badge = document.getElementById('notification-badge');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    /**
     * Show toast notification for new notification
     */
    showNewNotificationToast(notification) {
        const data = notification.data;
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'fixed top-20 right-4 bg-white rounded-lg shadow-lg p-4 max-w-sm z-50 transform transition-transform duration-300 translate-x-full';
        toast.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                    ${this.getNotificationIcon(data.icon)}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">${data.title}</p>
                    <p class="text-sm text-gray-600 mt-1">${data.message.substring(0, 100)}...</p>
                </div>
                <button class="ml-2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;

        // Add to DOM
        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 10);

        // Close button
        toast.querySelector('button').addEventListener('click', () => {
            this.closeToast(toast);
        });

        // Auto close after 5 seconds
        setTimeout(() => {
            this.closeToast(toast);
        }, 5000);

        // Make toast clickable
        toast.addEventListener('click', () => {
            if (data.action_url) {
                window.location.href = data.action_url;
            }
        });
    }

    /**
     * Close toast notification
     */
    closeToast(toast) {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }

    /**
     * Get notification icon HTML
     */
    getNotificationIcon(iconName) {
        const icons = {
            'calendar': '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'x-circle': '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'bell': '<svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>',
            'alert-triangle': '<svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            'message-square': '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>',
        };

        return icons[iconName] || icons['bell'];
    }

    /**
     * Format time ago
     */
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Ngayon lang';
        if (seconds < 3600) return `${Math.floor(seconds / 60)} minuto ang nakalipas`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)} oras ang nakalipas`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)} araw ang nakalipas`;
        
        return date.toLocaleDateString('tl-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }
}

// Initialize notification system when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.notificationSystem = new NotificationSystem();
    });
} else {
    window.notificationSystem = new NotificationSystem();
}
