import {useEffect, useRef, useState} from 'react';
import {useNavigate} from 'react-router-dom';
import {isAxiosError} from 'axios';
import api from '../lib/axios';

type Notification = {
    id: number;
    content: string;
    seen: boolean;
    created_at: string;
    from: {id: number; username: string};
    url: string | null;
};

type NotificationPage = {
    data: Notification[];
    current_page: number;
    last_page: number;
    total: number;
};

type NotificationResponse = {
    unread_count: number;
    notifications: NotificationPage;
};

function dateLabel(value: string): string {
    return new Date(value).toLocaleString([], {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function Notifications({className = ''}: {className?: string}) {
    const navigate = useNavigate();
    const root = useRef<HTMLDivElement>(null);
    const [open, setOpen] = useState(false);
    const [items, setItems] = useState<Notification[]>([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    async function loadNotifications(pageNumber = 1, append = false) {
        setLoading(true);
        setError('');

        try {
            const {data} = await api.get<NotificationResponse>('/api/v1/notifications', {
                params: {page: pageNumber},
            });
            setItems(current => append ? [...current, ...data.notifications.data] : data.notifications.data);
            setUnreadCount(data.unread_count);
            setPage(data.notifications.current_page);
            setLastPage(data.notifications.last_page);
        } catch (error) {
            setError(isAxiosError(error) ? error.response?.data?.message ?? 'Unable to load notifications.' : 'Unable to load notifications.');
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        void loadNotifications();
        const timer = setInterval(() => void loadNotifications(), 15000);

        return () => clearInterval(timer);
    }, []);

    useEffect(() => {
        function close(event: MouseEvent) {
            if (!root.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        }

        function closeWithEscape(event: KeyboardEvent) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        }

        document.addEventListener('mousedown', close);
        document.addEventListener('keydown', closeWithEscape);

        return () => {
            document.removeEventListener('mousedown', close);
            document.removeEventListener('keydown', closeWithEscape);
        };
    }, []);

    async function openNotification(notification: Notification) {
        try {
            const {data} = await api.post<{read: boolean; url: string | null}>(`/api/v1/notifications/${notification.id}/read`);
            setItems(current => current.map(item => item.id === notification.id ? {...item, seen: true} : item));
            if (!notification.seen) {
                setUnreadCount(current => Math.max(0, current - 1));
            }
            setOpen(false);
            if (data.url) {
                navigate(data.url);
            }
        } catch (error) {
            setError(isAxiosError(error) ? error.response?.data?.message ?? 'Unable to open notification.' : 'Unable to open notification.');
        }
    }

    function toggle() {
        setOpen(current => !current);
        if (!open) {
            void loadNotifications();
        }
    }

    return <div className={`notifications ${className}`} ref={root}>
        <button
            type="button"
            className={`notification-bell ${unreadCount > 0 ? 'has-unread' : ''}`}
            onClick={toggle}
            aria-label={unreadCount > 0 ? `${unreadCount} unread notifications` : 'Notifications'}
            aria-expanded={open}
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
            </svg>
            {unreadCount > 0 && <span className="notification-count">{unreadCount > 99 ? '99+' : unreadCount}</span>}
        </button>
        {open && <section className="notification-panel" aria-label="Notifications">
            <header><h2>Notifications</h2><span>{unreadCount} unread</span></header>
            <div className="notification-list">
                {loading && items.length === 0 && <p>Loading...</p>}
                {error && <p className="notification-error" role="alert">{error}</p>}
                {!loading && !error && items.length === 0 && <p>No notifications.</p>}
                {items.map(notification => <button
                    type="button"
                    key={notification.id}
                    className={notification.seen ? '' : 'unread'}
                    onClick={() => openNotification(notification)}
                >
                    <span className="notification-from">{notification.from.username}</span>
                    <span>{notification.content}</span>
                    <time dateTime={notification.created_at}>{dateLabel(notification.created_at)}</time>
                </button>)}
            </div>
            {page < lastPage && <button className="load-notifications" type="button" disabled={loading} onClick={() => loadNotifications(page + 1, true)}>
                {loading ? 'Loading...' : 'Load older'}
            </button>}
        </section>}
    </div>;
}
