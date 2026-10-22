/**
 * Notification System
 * Handles polling, display, and interaction with notifications
 */

class NotificationSystem {
    constructor(options = {}) {
        this.pollingInterval = options.pollingInterval || 5000; // 5 seconds for near-real-time
        this.lastChecked = null;
        this.unreadCount = 0;
        this.isPolling = false;
        this.pollTimer = null;
        this.shownToasts = new Set(); // Track shown toast notification IDs
        this.isDropdownOpen = false; // Track if dropdown is open
        
        // Get last visit timestamp from localStorage (to prevent old notifications from showing as toast)
        this.sessionStartTime = localStorage.getItem('notif_session_start') || new Date().toISOString();
        
        // Update session start time for this visit
        localStorage.setItem('notif_session_start', new Date().toISOString());
        
        // Initialize immediately
        this.init();
    }

    /**
     * Check if toast was already shown for this notification
     */
    hasShownToast(notificationId) {
        return this.shownToasts.has(notificationId);
    }

    /**
     * Mark toast as shown for this notification
     */
    markToastAsShown(notificationId) {
        this.shownToasts.add(notificationId);
    }

    /**
     * Initialize notification system
     */
    init() {
        // Find existing UI elements
        this.notificationBtn = document.getElementById('notificationBtn');
        this.notificationMenu = document.getElementById('notificationMenu');
        this.notificationList = document.getElementById('notificationList');
        this.notifBadge = document.getElementById('notifBadge');
        this.markAllReadBtn = document.getElementById('markAllReadBtn');

        if (!this.notificationBtn) {
            // Notification UI not present on this page - skip initialization
            return;
        }

        this.attachEventListeners();
        this.startPolling();
        
        // Load initial notifications
        this.loadNotifications();
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Toggle notification dropdown
        if (this.notificationBtn) {
            this.notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleNotificationMenu();
            });
        }

        // Mark all as read (desktop)
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
        
        // Mark all as read (mobile)
        const markAllReadBtnMobile = document.getElementById('markAllReadBtnMobile');
        if (markAllReadBtnMobile) {
            markAllReadBtnMobile.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
        
        // Clear read notifications button (desktop)
        const clearReadBtn = document.getElementById('clearReadBtn');
        if (clearReadBtn) {
            clearReadBtn.addEventListener('click', () => {
                this.clearReadNotifications();
            });
        }
        
        // Clear read notifications button (mobile)
        const clearReadBtnMobile = document.getElementById('clearReadBtnMobile');
        if (clearReadBtnMobile) {
            clearReadBtnMobile.addEventListener('click', () => {
                this.clearReadNotifications();
            });
        }
        
        // Mobile close button
        const closeNotificationMobile = document.getElementById('closeNotificationMobile');
        if (closeNotificationMobile) {
            closeNotificationMobile.addEventListener('click', () => {
                this.closeNotificationMenu();
            });
        }

        // Close dropdown when clicking outside (only on desktop)
        document.addEventListener('click', (e) => {
            if (this.notificationMenu && 
                !this.notificationMenu.contains(e.target) && 
                !this.notificationBtn.contains(e.target)) {
                this.closeNotificationMenu();
            }
        });
    }
    
    /**
     * Close notification menu and restore body scroll
     */
    closeNotificationMenu() {
        if (this.notificationMenu) {
            this.notificationMenu.classList.add('hidden');
            this.isDropdownOpen = false;
            // Restore body scroll on mobile
            document.body.style.overflow = '';
        }
    }

    /**
     * Toggle notification dropdown
     */
    toggleNotificationMenu() {
        if (this.notificationMenu) {
            // Close profile menu if open
            const profileMenu = document.getElementById('profileMenu');
            if (profileMenu && !profileMenu.classList.contains('hidden')) {
                profileMenu.classList.add('hidden');
            }
            
            this.notificationMenu.classList.toggle('hidden');
            this.isDropdownOpen = !this.notificationMenu.classList.contains('hidden');
            
            // Lock body scroll on mobile when modal is open
            if (this.isDropdownOpen && window.innerWidth < 640) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
            
            if (this.isDropdownOpen) {
                this.loadNotifications();
            }
        }
    }
    
    /**
     * Clear all read notifications
     */
    async clearReadNotifications() {
        try {
            const response = await fetch('/api/notifications/clear-read', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            });

            const data = await response.json();

            if (data.success) {
                // Show success feedback
                this.showToast(`Cleared ${data.deleted_count} read notification${data.deleted_count !== 1 ? 's' : ''}`, 'success');
                // Reload notifications
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Error clearing read notifications:', error);
            this.showToast('Failed to clear notifications', 'error');
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
                    // Update badge counter
                    this.updateUnreadCount(data.unread_count);
                    
                    // Auto-refresh dropdown if it's open
                    if (this.isDropdownOpen) {
                        this.loadNotifications();
                    }
                    
                    // Show toast for NEW unread notifications
                    if (data.has_new && data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            // Only show toast if: unread AND not shown before AND created after session start
                            const notificationTime = new Date(notification.created_at);
                            const sessionTime = new Date(this.sessionStartTime);
                            
                            if (!notification.read_at && 
                                !this.hasShownToast(notification.id) && 
                                notificationTime > sessionTime) {
                                this.showNewNotificationToast(notification);
                                this.markToastAsShown(notification.id);
                            }
                        });
                    }
                    
                    this.lastChecked = data.timestamp;
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
        const list = this.notificationList || document.getElementById('notificationList');
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
                list.innerHTML = '<div class="p-8 text-center text-red-500">May error sa pag-load</div>';
            }
        } catch (error) {
            console.error('Load notifications error:', error);
            list.innerHTML = '<div class="p-8 text-center text-red-500">May error sa pag-load</div>';
        }
    }

    /**
     * Render notifications in dropdown
     */
    renderNotifications(notifications) {
        const list = this.notificationList || document.getElementById('notificationList');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = `
                <div class="p-12 sm:p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 sm:w-12 sm:h-12 mx-auto mb-3 sm:mb-2 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155 6.852a3 3 0 005.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 003.536-1.003A8.967 8.967 0 0118 9.75V9A6 6 0 006.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53"/>
                    </svg>
                    <p class="text-base sm:text-sm">No notifications</p>
                </div>
            `;
            return;
        }

        // Remove duplicates by ID (fix for Brave browser)
        const uniqueNotifications = Array.from(
            new Map(notifications.map(n => [n.id, n])).values()
        );

        list.innerHTML = uniqueNotifications.map(notification => {
            const data = notification.data;
            const isUnread = !notification.read_at;
            const icon = this.getNotificationIcon(data.icon);
            
            return `
                <div class="notification-item ${isUnread ? 'bg-blue-50' : 'bg-white'} border-b border-gray-200 p-5 sm:p-4 hover:bg-gray-50 cursor-pointer active:bg-gray-100"
                     data-notification-id="${notification.id}"
                     data-action-url="${data.action_url || '#'}">
                    <div class="flex items-start gap-3 sm:gap-3">
                        <div class="flex-shrink-0">
                            ${icon}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-base sm:text-sm font-medium text-gray-900 leading-snug">
                                ${data.title}
                            </p>
                            <p class="text-sm sm:text-sm text-gray-600 mt-1.5 sm:mt-1 leading-relaxed">
                                ${data.message}
                            </p>
                            <p class="text-sm sm:text-xs text-gray-400 mt-2 sm:mt-2">
                                ${this.formatTime(notification.created_at)}
                            </p>
                        </div>
                        ${isUnread ? '<div class="ml-2 w-2.5 h-2.5 sm:w-2 sm:h-2 bg-blue-600 rounded-full flex-shrink-0 mt-1"></div>' : ''}
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
        // Mark as read and reload the notification list
        await this.markAsRead(id);
        await this.loadNotifications();
    }

    /**
     * Mark notification as read
     */
    async markAsRead(id) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch(`/api/notifications/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
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
        const badge = this.notifBadge || document.getElementById('notifBadge');
        
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
        const closeBtn = toast.querySelector('button');
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent triggering toast click event
            this.closeToast(toast);
        });

        // Auto close after 5 seconds
        setTimeout(() => {
            this.closeToast(toast);
        }, 5000);

        // Make toast clickable (except close button)
        toast.addEventListener('click', (e) => {
            if (!closeBtn.contains(e.target)) {
                // Mark notification as read before navigating
                this.markAsRead(notification.id);
                if (data.action_url) {
                    window.location.href = data.action_url;
                }
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
     * Show a simple toast message
     */
    showToast(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
        };
        
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /**
     * Get notification icon HTML
     */
    getNotificationIcon(iconName) {
        const icons = {
            'calendar': '<svg class="w-8 h-8 sm:w-6 sm:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'x-circle': '<svg class="w-8 h-8 sm:w-6 sm:h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'bell': '<svg class="w-8 h-8 sm:w-6 sm:h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>',
            'alert-triangle': '<svg class="w-8 h-8 sm:w-6 sm:h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            'message-square': '<svg class="w-8 h-8 sm:w-6 sm:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>',
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

// Initialize notification system when DOM is fully ready
// Prevent multiple initializations
if (!window.notificationSystem) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (!window.notificationSystem) {
                    window.notificationSystem = new NotificationSystem();
                }
            }, 150);
        });
    } else {
        setTimeout(() => {
            if (!window.notificationSystem) {
                window.notificationSystem = new NotificationSystem();
            }
        }, 150);
    }
}
