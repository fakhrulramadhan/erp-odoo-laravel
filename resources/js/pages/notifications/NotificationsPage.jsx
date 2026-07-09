import { useState, useEffect } from 'react';
import { dashboardApi } from '../../api/endpoints';

const typeIcons = {
    info: { bg: 'bg-blue-100', text: 'text-blue-600', icon: 'ℹ' },
    warning: { bg: 'bg-yellow-100', text: 'text-yellow-600', icon: '⚠' },
    error: { bg: 'bg-red-100', text: 'text-red-600', icon: '✕' },
    success: { bg: 'bg-green-100', text: 'text-green-600', icon: '✓' },
};

export default function NotificationsPage() {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchNotifications = async () => {
        setLoading(true);
        try {
            const res = await dashboardApi.notifications();
            const data = res.data?.data;
            setNotifications(Array.isArray(data) ? data : data?.data || []);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load notifications');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { fetchNotifications(); }, []);

    const handleMarkRead = async (id) => {
        try {
            await dashboardApi.markRead(id);
            setNotifications(prev =>
                prev.map(n => n.id === id ? { ...n, read_at: new Date().toISOString() } : n)
            );
        } catch { /* ignore */ }
    };

    const handleMarkAllRead = async () => {
        try {
            await dashboardApi.markAllRead();
            setNotifications(prev =>
                prev.map(n => ({ ...n, read_at: n.read_at || new Date().toISOString() }))
            );
        } catch { /* ignore */ }
    };

    const unreadCount = notifications.filter(n => !n.read_at).length;

    if (loading) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        {unreadCount > 0 ? `${unreadCount} unread notification${unreadCount > 1 ? 's' : ''}` : 'All caught up!'}
                    </p>
                </div>
                {unreadCount > 0 && (
                    <button
                        onClick={handleMarkAllRead}
                        className="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                        Mark all as read
                    </button>
                )}
            </div>

            {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    {error}
                </div>
            )}

            {/* Notification List */}
            {notifications.length === 0 ? (
                <div className="text-center py-16">
                    <div className="text-6xl mb-4">🔔</div>
                    <h3 className="text-lg font-medium text-gray-900">No notifications</h3>
                    <p className="text-gray-500 mt-1">You're all caught up! Check back later.</p>
                </div>
            ) : (
                <div className="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-100">
                    {notifications.map((notification) => {
                        const isUnread = !notification.read_at;
                        const type = typeIcons[notification.type] || typeIcons.info;

                        return (
                            <div
                                key={notification.id}
                                className={`flex items-start gap-4 p-4 transition-colors ${
                                    isUnread ? 'bg-blue-50/40' : 'hover:bg-gray-50'
                                }`}
                            >
                                {/* Icon */}
                                <div className={`flex-shrink-0 w-10 h-10 rounded-full ${type.bg} ${type.text} flex items-center justify-center text-lg font-bold`}>
                                    {type.icon}
                                </div>

                                {/* Content */}
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2">
                                        <h4 className={`text-sm ${isUnread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700'}`}>
                                            {notification.title || 'Notification'}
                                        </h4>
                                        {isUnread && (
                                            <span className="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0" />
                                        )}
                                    </div>
                                    <p className="text-sm text-gray-500 mt-0.5 line-clamp-2">
                                        {notification.message || notification.body || '-'}
                                    </p>
                                    <p className="text-xs text-gray-400 mt-1">
                                        {notification.created_at
                                            ? new Date(notification.created_at).toLocaleString('id-ID', {
                                                day: 'numeric', month: 'short', year: 'numeric',
                                                hour: '2-digit', minute: '2-digit',
                                            })
                                            : '-'
                                        }
                                    </p>
                                </div>

                                {/* Actions */}
                                {isUnread && (
                                    <button
                                        onClick={() => handleMarkRead(notification.id)}
                                        className="flex-shrink-0 text-xs text-blue-600 hover:text-blue-800 font-medium mt-1"
                                    >
                                        Mark read
                                    </button>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
