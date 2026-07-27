<?php
if (!defined('NOTIFICATION_CENTER_MOUNTED')) {
    define('NOTIFICATION_CENTER_MOUNTED', true);
    $isAuthenticated = isset($_SESSION['user_id']);
    $notifEndpoints = [
        'list' => '/Project/public/index.php?page=notifications',
        'badge' => '/Project/public/index.php?page=notifications&action=badge',
        'markOne' => '/Project/public/index.php?page=notifications&action=mark-read',
        'markAll' => '/Project/public/index.php?page=notifications&action=mark-all',
    ];
    ?>
    <style>
        #notification-center {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 1200;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 14px;
        }
        .notification-bubble {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #ff7426, #ff9f5e);
            color: #fff;
            font-size: 1.3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(255, 116, 38, 0.35);
            cursor: pointer;
        }
        .theme-toggle-bubble {
            background: linear-gradient(135deg, #1d7dfa, #4cb0ff);
            box-shadow: 0 10px 30px rgba(29, 125, 250, 0.35);
            font-size: 1.25rem;
        }
        .theme-toggle-bubble .theme-state-label {
            position: absolute;
            bottom: -18px;
            right: 50%;
            transform: translateX(50%);
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .notification-bubble:focus-visible {
            outline: 3px solid rgba(255, 255, 255, 0.8);
            outline-offset: 3px;
        }
        .notification-bubble .notif-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ff1744;
            min-width: 20px;
            height: 20px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }
        .notification-overlay {
            position: fixed;
            inset: 0;
            background: rgba(8, 18, 37, 0.45);
            backdrop-filter: blur(2px);
            z-index: 1198;
        }
        .notification-drawer {
            position: fixed;
            top: 0;
            right: -440px;
            width: min(420px, 100%);
            height: 100vh;
            background: #fff;
            box-shadow: -8px 0 30px rgba(8, 18, 37, 0.15);
            display: flex;
            flex-direction: column;
            z-index: 1199;
            transition: right 0.3s ease;
        }
        .notification-drawer.open {
            right: 0;
        }
        .notification-drawer header {
            padding: 20px 24px 12px;
            border-bottom: 1px solid rgba(15, 28, 54, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification-drawer header h5 {
            margin: 0;
            font-weight: 700;
        }
        .notification-drawer button.close-btn {
            border: none;
            background: transparent;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .notification-drawer .notification-actions {
            padding: 12px 24px;
            display: flex;
            gap: 10px;
        }
        .notification-drawer .notification-actions button {
            border-radius: 999px;
            border: 1px solid rgba(15, 28, 54, 0.15);
            background: #f6f7fb;
            padding: 6px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .notification-drawer .notification-list {
            list-style: none;
            margin: 0;
            padding: 0 24px 24px;
            overflow-y: auto;
            flex: 1;
        }
        .notification-drawer .notification-item {
            border: 1px solid rgba(15, 28, 54, 0.08);
            border-radius: 16px;
            padding: 14px 16px;
            margin-top: 14px;
            background: #fff;
            box-shadow: 0 10px 20px rgba(8, 18, 37, 0.08);
        }
        .notification-drawer .notification-item.unread {
            border-color: rgba(255, 116, 38, 0.4);
            background: rgba(255, 244, 238, 0.7);
        }
        .notification-drawer .notification-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .notification-drawer .notification-type {
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            text-transform: uppercase;
        }
        .notification-empty {
            text-align: center;
            padding: 40px 24px;
            color: #7a889f;
            font-size: 0.95rem;
        }
        @media (max-width: 576px) {
            #notification-center {
                top: 16px;
                right: 16px;
            }
            .notification-drawer {
                width: 100%;
            }
        }
    </style>
    <div id="notification-center">
        <button
            class="notification-bubble"
            type="button"
            aria-label="Notifications"
            data-role="notification-bubble"
            data-endpoint-list="<?= $notifEndpoints['list']; ?>"
            data-endpoint-badge="<?= $notifEndpoints['badge']; ?>"
            data-endpoint-mark-one="<?= $notifEndpoints['markOne']; ?>"
            data-endpoint-mark-all="<?= $notifEndpoints['markAll']; ?>"
            data-authenticated="<?= $isAuthenticated ? '1' : '0'; ?>"
        >
            <i class="bi bi-bell"></i>
            <span class="notif-count" data-role="notification-count">0</span>
        </button>
        <button
            class="notification-bubble theme-toggle-bubble"
            type="button"
            aria-label="Activer le mode clair"
            data-role="theme-toggle"
        >
            <i class="bi bi-sun"></i>
            <span class="theme-state-label">Thème</span>
        </button>
        <div class="notification-overlay" id="notificationOverlay" hidden></div>
        <aside class="notification-drawer" id="notificationDrawer" aria-hidden="true">
            <header>
                <div>
                    <small class="text-muted text-uppercase">Flux</small>
                    <h5>Notifications</h5>
                </div>
                <button class="close-btn" type="button" data-role="notification-close" aria-label="Fermer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </header>
            <div class="notification-actions">
                <button type="button" data-role="notification-refresh"><i class="bi bi-arrow-repeat me-1"></i>Actualiser</button>
                <button type="button" data-role="notification-mark-all"><i class="bi bi-check2-all me-1"></i>Tout lu</button>
            </div>
            <div class="notification-empty" data-role="notification-empty">
                Aucune notification pour le moment.
            </div>
            <ul class="notification-list" data-role="notification-list"></ul>
        </aside>
    </div>
    <script>
        (function () {
            const bubble = document.querySelector('[data-role="notification-bubble"]');
            if (!bubble) {
                return;
            }
            const drawer = document.getElementById('notificationDrawer');
            const overlay = document.getElementById('notificationOverlay');
            const listEl = drawer.querySelector('[data-role="notification-list"]');
            const emptyEl = drawer.querySelector('[data-role="notification-empty"]');
            const badgeEl = bubble.querySelector('[data-role="notification-count"]');
            const refreshBtn = drawer.querySelector('[data-role="notification-refresh"]');
            const markAllBtn = drawer.querySelector('[data-role="notification-mark-all"]');
            const closeBtn = drawer.querySelector('[data-role="notification-close"]');

            const isAuthenticated = bubble.dataset.authenticated === '1';

            const endpoints = {
                list: bubble.dataset.endpointList,
                badge: bubble.dataset.endpointBadge,
                markOne: bubble.dataset.endpointMarkOne,
                markAll: bubble.dataset.endpointMarkAll,
            };

            const fetchJSON = async (url, options = {}) => {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    ...options,
                });
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            };

            const toggleDrawer = (open) => {
                if (open) {
                    drawer.classList.add('open');
                    drawer.setAttribute('aria-hidden', 'false');
                    overlay.hidden = false;
                    if (isAuthenticated) {
                        refreshNotifications();
                    } else {
                        emptyEl.hidden = false;
                        emptyEl.textContent = 'Connectez-vous pour consulter vos notifications.';
                        listEl.innerHTML = '';
                    }
                } else {
                    drawer.classList.remove('open');
                    drawer.setAttribute('aria-hidden', 'true');
                    overlay.hidden = true;
                }
            };

            const refreshBadge = async () => {
                if (!isAuthenticated) {
                    return;
                }
                try {
                    const data = await fetchJSON(endpoints.badge);
                    const count = Number(data.unread) || 0;
                    badgeEl.textContent = count > 99 ? '99+' : count;
                    badgeEl.hidden = count === 0;
                } catch (error) {
                    console.error('Notification badge error', error);
                }
            };

            const refreshNotifications = async () => {
                if (!isAuthenticated) {
                    listEl.innerHTML = '<li class="text-center py-4 text-muted">Connectez-vous pour consulter vos notifications.</li>';
                    emptyEl.hidden = true;
                    return;
                }
                listEl.innerHTML = '<li class="text-center py-4 text-muted">Chargement...</li>';
                try {
                    const data = await fetchJSON(endpoints.list + '&limit=15&notif_page=1');
                    renderNotifications(data.notifications || []);
                    const count = Number(data.unread) || 0;
                    badgeEl.textContent = count > 99 ? '99+' : count;
                    badgeEl.hidden = count === 0;
                } catch (error) {
                    listEl.innerHTML = '<li class="text-center py-4 text-danger">Impossible de charger les notifications.</li>';
                    console.error('Notification list error', error);
                }
            };

            const renderNotifications = (items) => {
                listEl.innerHTML = '';
                if (!items.length) {
                    emptyEl.hidden = false;
                    return;
                }
                emptyEl.hidden = true;
                items.forEach((notif) => {
                    const li = document.createElement('li');
                    li.className = 'notification-item' + (Number(notif.lu) === 0 ? ' unread' : '');
                    li.dataset.id = notif.id;
                    li.innerHTML = `
                        <div class="notification-meta">
                            <span class="notification-type">${(notif.type || 'info').toUpperCase()}</span>
                            <small class="text-muted">${notif.date_formattee || ''}</small>
                        </div>
                        <strong>${notif.titre || 'Notification'}</strong>
                        <p class="mb-2 text-muted small">${notif.contenu || ''}</p>
                        <div class="d-flex gap-2 flex-wrap">
                            ${notif.lien ? `<a class="btn btn-sm btn-outline-primary" href="${notif.lien}" target="_blank" rel="noopener">Ouvrir</a>` : ''}
                            ${Number(notif.lu) === 0 ? `<button type="button" class="btn btn-sm btn-primary" data-action="mark-read" data-id="${notif.id}"><i class="bi bi-check2"></i> Lu</button>` : ''}
                        </div>
                    `;
                    listEl.appendChild(li);
                });
            };

            const markNotification = async (id) => {
                if (!isAuthenticated) {
                    return;
                }
                try {
                    await fetchJSON(endpoints.markOne, {
                        method: 'POST',
                        body: JSON.stringify({ id }),
                    });
                    await refreshNotifications();
                } catch (error) {
                    console.error('Notification mark error', error);
                }
            };

            const markAll = async () => {
                if (!isAuthenticated) {
                    return;
                }
                try {
                    await fetchJSON(endpoints.markAll, { method: 'POST', body: '{}' });
                    await refreshNotifications();
                } catch (error) {
                    console.error('Notification mark-all error', error);
                }
            };

            bubble.addEventListener('click', () => toggleDrawer(true));
            closeBtn.addEventListener('click', () => toggleDrawer(false));
            overlay.addEventListener('click', () => toggleDrawer(false));
            if (isAuthenticated) {
                refreshBtn.addEventListener('click', refreshNotifications);
                markAllBtn.addEventListener('click', markAll);
            } else {
                refreshBtn.addEventListener('click', () => {
                    listEl.innerHTML = '<li class="text-center py-4 text-muted">Connectez-vous pour utiliser cette action.</li>';
                    emptyEl.hidden = true;
                });
                markAllBtn.addEventListener('click', () => {
                    listEl.innerHTML = '<li class="text-center py-4 text-muted">Connectez-vous pour utiliser cette action.</li>';
                    emptyEl.hidden = true;
                });
            }
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    toggleDrawer(false);
                }
            });
            listEl.addEventListener('click', (event) => {
                const target = event.target.closest('[data-action="mark-read"]');
                if (!target) {
                    return;
                }
                const id = Number(target.dataset.id || 0);
                if (id > 0) {
                    markNotification(id);
                }
            });

            if (isAuthenticated) {
                refreshBadge();
                setInterval(refreshBadge, 60000);
            } else {
                badgeEl.hidden = true;
            }
        })();

        (function () {
            const STORAGE_KEY = 'meditrack-theme';
            const root = document.documentElement;
            const body = document.body;
            const toggleBtn = document.querySelector('[data-role="theme-toggle"]');
            if (!toggleBtn) {
                return;
            }
            const icon = toggleBtn.querySelector('i');
            const mediaQuery = typeof window.matchMedia === 'function'
                ? window.matchMedia('(prefers-color-scheme: light)')
                : null;

            const storage = {
                get() {
                    try {
                        return window.localStorage ? window.localStorage.getItem(STORAGE_KEY) : null;
                    } catch (error) {
                        console.warn('Theme storage get error', error);
                        return null;
                    }
                },
                set(value) {
                    try {
                        if (window.localStorage) {
                            window.localStorage.setItem(STORAGE_KEY, value);
                        }
                    } catch (error) {
                        console.warn('Theme storage set error', error);
                    }
                },
            };

            const updateToggleVisual = (theme) => {
                const isLight = theme === 'light';
                toggleBtn.setAttribute('aria-label', isLight ? 'Activer le mode sombre' : 'Activer le mode clair');
                toggleBtn.dataset.themeCurrent = theme;
                if (icon) {
                    icon.classList.remove('bi-sun', 'bi-moon-stars');
                    icon.classList.add(isLight ? 'bi-moon-stars' : 'bi-sun');
                }
                const label = toggleBtn.querySelector('.theme-state-label');
                if (label) {
                    label.textContent = isLight ? 'Clair' : 'Sombre';
                }
            };

            const setTheme = (theme, persist = true) => {
                const normalized = theme === 'light' ? 'light' : 'dark';
                root.dataset.theme = normalized;
                body.dataset.theme = normalized;
                if (persist) {
                    storage.set(normalized);
                }
                updateToggleVisual(normalized);
            };

            const initTheme = () => {
                const stored = storage.get();
                if (stored === 'light' || stored === 'dark') {
                    setTheme(stored, false);
                    return;
                }
                const prefersLight = mediaQuery ? mediaQuery.matches : false;
                setTheme(prefersLight ? 'light' : 'dark', false);
            };

            toggleBtn.addEventListener('click', () => {
                const current = (root.dataset.theme || 'dark') === 'light' ? 'light' : 'dark';
                setTheme(current === 'light' ? 'dark' : 'light');
            });

            if (mediaQuery) {
                const mediaHandler = (event) => {
                    const stored = storage.get();
                    if (stored !== null) {
                        return;
                    }
                    setTheme(event.matches ? 'light' : 'dark', false);
                };
                if (typeof mediaQuery.addEventListener === 'function') {
                    mediaQuery.addEventListener('change', mediaHandler);
                } else if (typeof mediaQuery.addListener === 'function') {
                    mediaQuery.addListener(mediaHandler);
                }
            }

            initTheme();
        })();
    </script>
    <?php
}
?>
