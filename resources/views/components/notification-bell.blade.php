{{--
    Notification bell — polls unread-count endpoint every 30s.
--}}
<div class="notification-bell"
     x-data="notificationBell({ url: '{{ route('notifications.unread') }}', listUrl: '{{ route('notifications.index') }}', readUrlBase: '{{ url('/notifications') }}' })"
     x-init="init()">

    <button class="btn btn-link notification-bell-btn" @click="open = !open" type="button">
        <i class="bi bi-bell-fill"></i>
        <span class="notification-badge" x-show="count > 0" x-text="count > 9 ? '9+' : count"></span>
    </button>

    <div class="notification-dropdown" x-show="open" @click.outside="open = false" x-cloak x-transition>
        <div class="notification-dropdown-header">
            <strong>Notifications</strong>
            <span class="badge bg-primary" x-text="count + ' new'"></span>
        </div>

        <template x-if="recent.length === 0">
            <div class="notification-empty">
                <i class="bi bi-bell-slash"></i>
                <p class="mb-0 mt-2 small">No new notifications</p>
            </div>
        </template>

        <template x-for="n in recent" :key="n.notification_id">
            <a :href="n.link || '{{ route('notifications.index') }}'"
               class="notification-item"
               @click.prevent="markRead(n)">
                <div class="notification-item-title" x-text="n.subject"></div>
                <div class="notification-item-body" x-text="n.notification_message"></div>
                <div class="notification-item-time" x-text="formatTime(n.timestamp)"></div>
            </a>
        </template>

        <div class="notification-dropdown-footer">
            <a :href="listUrl">View all notifications</a>
        </div>
    </div>
</div>
