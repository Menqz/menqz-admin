admin.notification = {
    _initialized: false,
    _refreshTimer: null,
    _pusher: null,
    _subscriptions: [],
    _baselineLoaded: false,
    _seenIds: null,
    _lastCount: 0,

    init: function () {
        if (this._initialized) {
            return;
        }

        const toggle = document.getElementById('menqz-admin-notification-link');
        if (!toggle) {
            return;
        }

        this._initialized = true;
        this._seenIds = new Set();

        this._config = {
            unreadUrl: toggle.dataset.unreadUrl,
            readUrlTemplate: toggle.dataset.readUrlTemplate,
            readAllUrl: toggle.dataset.readAllUrl,
            indexUrl: toggle.dataset.indexUrl,
            broadcastAuthUrl: toggle.dataset.broadcastAuthUrl,
            userId: parseInt(toggle.dataset.userId || '0', 10),
            roleIds: [],
            pusherEnabled: toggle.dataset.pusherEnabled === '1',
            pusherKey: toggle.dataset.pusherKey || '',
            pusherCluster: toggle.dataset.pusherCluster || '',
            pusherForceTls: toggle.dataset.pusherForceTls === '1',
        };

        try {
            this._config.roleIds = JSON.parse(toggle.dataset.roleIds || '[]') || [];
        } catch (e) {
            this._config.roleIds = [];
        }

        this._badge = document.getElementById('menqz-admin-notification-badge');
        this._panel = document.getElementById('menqz-admin-notification-panel');
        this._items = document.getElementById('menqz-admin-notification-items');
        this._summary = document.getElementById('menqz-admin-notification-summary');
        this._count = document.getElementById('menqz-admin-notification-count');
        this._readAllHeader = document.getElementById('menqz-admin-notification-read-all-header');

        this.bindEvents();
        this.refresh();
        this.setupPusher();
        this.setupPolling();
    },

    bindEvents: function () {
        const self = this;

        if (this._readAllHeader) {
            this._readAllHeader.addEventListener('click', function (event) {
                event.preventDefault();
                self.readAll();
            });
        }

        if (this._panel) {
            this._panel.addEventListener('show.bs.offcanvas', function () {
                self.refresh();
            });
        }

        document.addEventListener('click', function (event) {
            const dismissBtn = event.target.closest('.menqz-admin-notification-dismiss');
            if (dismissBtn) {
                event.preventDefault();
                const id = dismissBtn.dataset.id;
                const readUrl = dismissBtn.dataset.readUrl || self.getReadUrl(id);
                self.readByUrl(readUrl);
                return;
            }

            const readBtn = event.target.closest('.menqz-admin-notification-read');
            if (readBtn) {
                event.preventDefault();
                const id = readBtn.dataset.id;
                const readUrl = readBtn.dataset.readUrl || self.getReadUrl(id);
                self.readByUrl(readUrl);
                return;
            }

            const readAllBtn = event.target.closest('#menqz-admin-notification-read-all');
            if (readAllBtn) {
                event.preventDefault();
                const url = readAllBtn.dataset.readAllUrl || self._config.readAllUrl;
                self.readAll(url);
                return;
            }

            const actionLink = event.target.closest('.menqz-admin-notification-action');
            if (actionLink) {
                event.preventDefault();
                const item = actionLink.closest('[data-menqz-admin-notification-id]');
                if (item) {
                    self.readAndRedirect(item.dataset.menqzAdminNotificationId, item.dataset.redirectUrl);
                }
                return;
            }

            const item = event.target.closest('[data-menqz-admin-notification-id]');
            if (item) {
                event.preventDefault();
                const id = item.dataset.menqzAdminNotificationId;
                self.readAndRedirect(id, item.dataset.redirectUrl);
                return;
            }
        });
    },

    setupPolling: function () {
        if (this._config.pusherEnabled) {
            return;
        }

        const self = this;
        this._refreshTimer = setInterval(function () {
            self.refresh();
        }, 60000);
    },

    setupPusher: function () {
        if (!this._config.pusherEnabled) {
            return;
        }

        if (!this._config.pusherKey || typeof Pusher === 'undefined') {
            return;
        }

        const self = this;

        this._pusher = new Pusher(this._config.pusherKey, {
            cluster: this._config.pusherCluster,
            forceTLS: this._config.pusherForceTls,
            authEndpoint: this._config.broadcastAuthUrl,
            auth: {
                headers: {
                    'X-CSRF-TOKEN': LA.token,
                },
            },
        });

        if (this._config.userId) {
            const channel = this._pusher.subscribe('private-menqz-admin.notifications.user.' + this._config.userId);
            channel.bind('menqz-admin.notification.created', function (payload) {
                self.onIncoming(payload);
            });
            this._subscriptions.push(channel);
        }

        if (Array.isArray(this._config.roleIds)) {
            this._config.roleIds.forEach(function (roleId) {
                const channel = self._pusher.subscribe('private-menqz-admin.notifications.role.' + roleId);
                channel.bind('menqz-admin.notification.created', function (payload) {
                    self.onIncoming(payload);
                });
                self._subscriptions.push(channel);
            });
        }
    },

    onIncoming: function (payload) {
        if (payload && payload.id) {
            this._seenIds.add(String(payload.id));
        }

        if (payload && (payload.title || payload.description)) {
            this.showToast(payload.title || 'Notificação', payload.description || '');
        }

        this.refresh();
    },

    refresh: function () {
        if (!this._config || !this._config.unreadUrl) {
            return;
        }

        const self = this;

        axios({
            method: 'get',
            url: this._config.unreadUrl,
            headers: { Accept: 'application/json' },
        })
        .then(function (response) {
            const data = response.data || {};
            self.onRefreshData(data.count || 0, data.notifications || []);
        })
        .catch(function () {});
    },

    onRefreshData: function (count, notifications) {
        if (!this._baselineLoaded) {
            (notifications || []).forEach((n) => {
                if (n && n.id) {
                    this._seenIds.add(String(n.id));
                }
            });
            this._baselineLoaded = true;
            this._lastCount = count;
            this.render(count, notifications);
            return;
        }

        const newOnes = (notifications || []).filter((n) => n && n.id && !this._seenIds.has(String(n.id)));

        if (newOnes.length) {
            newOnes.forEach((n) => this._seenIds.add(String(n.id)));

            const first = newOnes[0];
            this.showToast(first.title || 'Notificação', first.description || '');
        }

        this._lastCount = count;
        this.render(count, notifications);
    },

    showToast: function (title, description) {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: title,
            text: description,
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });
    },

    render: function (count, notifications) {
        if (this._badge) {
            if (count > 0) {
                this._badge.textContent = count > 99 ? '99+' : String(count);
                this._badge.classList.remove('d-none');
            } else {
                this._badge.textContent = '';
                this._badge.classList.add('d-none');
            }
        }

        if (this._count) {
            this._count.textContent = count > 99 ? '99+' : String(count);
        }

        if (this._readAllHeader) {
            this._readAllHeader.disabled = count === 0;
        }

        if (!this._items) {
            return;
        }

        this._items.innerHTML = '';

        if (!notifications.length) {
            const empty = document.createElement('div');
            empty.className = 'menqz-admin-notification-empty';
            empty.innerHTML = '<i class="icon-bell"></i><div class="fw-semibold mb-1">Sem notificacoes</div><div class="small">Quando houver novidades, elas aparecerao aqui.</div>';
            this._items.appendChild(empty);
            return;
        }

        notifications.forEach((n) => {
            const item = document.createElement('div');
            item.className = 'menqz-admin-notification-item';
            item.dataset.menqzAdminNotificationId = n.id;
            item.dataset.redirectUrl = this.getRedirectUrl(n);

            const icon = document.createElement('div');
            icon.className = 'menqz-admin-notification-icon';
            icon.innerHTML = '<i class="' + this.resolveIconClass(n.icon) + '"></i>';

            const body = document.createElement('div');
            body.className = 'menqz-admin-notification-body';

            const titleRow = document.createElement('div');
            titleRow.className = 'menqz-admin-notification-title-row';

            const title = document.createElement('div');
            title.className = 'menqz-admin-notification-title';
            title.textContent = n.title || '';

            const dismiss = document.createElement('button');
            dismiss.type = 'button';
            dismiss.className = 'menqz-admin-notification-dismiss';
            dismiss.dataset.id = n.id;
            dismiss.dataset.readUrl = this.getReadUrl(n.id);
            dismiss.setAttribute('aria-label', 'Marcar como lida');
            dismiss.innerHTML = '<i class="icon-times"></i>';

            const meta = document.createElement('div');
            meta.className = 'menqz-admin-notification-meta';

            const time = document.createElement('span');
            time.className = 'menqz-admin-notification-time';
            time.textContent = this.formatRelativeDate(n.created_at);

            const desc = document.createElement('p');
            desc.className = 'menqz-admin-notification-description';
            desc.textContent = n.description || '';

            const action = document.createElement('a');
            action.href = this.getRedirectUrl(n);
            action.className = 'menqz-admin-notification-action';
            action.textContent = n.redirect_title || 'Visualizar';

            titleRow.appendChild(title);
            titleRow.appendChild(dismiss);
            meta.appendChild(time);
            body.appendChild(titleRow);
            body.appendChild(meta);
            body.appendChild(desc);
            body.appendChild(action);
            item.appendChild(icon);
            item.appendChild(body);
            this._items.appendChild(item);
        });
    },

    formatRelativeDate: function (value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        const diffInSeconds = Math.round((date.getTime() - Date.now()) / 1000);
        const units = [
            { size: 60, name: 'second' },
            { size: 60, name: 'minute' },
            { size: 24, name: 'hour' },
            { size: 7, name: 'day' },
            { size: 4.34524, name: 'week' },
            { size: 12, name: 'month' },
            { size: Number.POSITIVE_INFINITY, name: 'year' },
        ];

        let valueToFormat = diffInSeconds;
        let unitName = 'second';

        for (let i = 0; i < units.length; i++) {
            const unit = units[i];

            if (Math.abs(valueToFormat) < unit.size) {
                unitName = unit.name;
                break;
            }

            valueToFormat /= unit.size;
        }

        return new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' }).format(Math.round(valueToFormat), unitName);
    },

    resolveIconClass: function (icon) {
        if (!icon) {
            return 'icon-bell';
        }

        if (icon.indexOf('icon-') === 0 || icon.indexOf('fa') === 0) {
            return icon;
        }

        return 'icon-' + icon;
    },

    getRedirectUrl: function (notification) {
        if (notification && notification.redirect_url) {
            return notification.redirect_url;
        }

        return this.getNotificationUrl(notification ? notification.id : null);
    },

    getReadUrl: function (id) {
        return (this._config.readUrlTemplate || '').replace('__ID__', String(id));
    },

    getNotificationUrl: function (id) {
        const indexUrl = (this._config.indexUrl || '').replace(/\/$/, '');

        return id ? indexUrl + '/' + id : indexUrl;
    },

    read: function (id) {
        const url = this.getReadUrl(id);
        return this.readByUrl(url);
    },

    readAndRedirect: function (id, targetUrl) {
        const self = this;
        const redirectUrl = targetUrl || this.getNotificationUrl(id);

        if (!redirectUrl) {
            return this.read(id);
        }

        return this.readByUrl(this.getReadUrl(id), false)
            .then(function () {
                window.location.href = redirectUrl;
            })
            .catch(function () {
                window.location.href = redirectUrl;
            });
    },

    readByUrl: function (url, shouldRefresh = true) {
        const self = this;
        if (!url) {
            return Promise.resolve();
        }

        return axios({
            method: 'post',
            url: url,
            data: { _token: LA.token },
            headers: { Accept: 'application/json' },
        })
        .then(function () {
            if (shouldRefresh) {
                self.refresh();
            }
        })
        .catch(function () {});
    },

    readAll: function (url) {
        const self = this;
        const targetUrl = url || this._config.readAllUrl;
        if (!targetUrl) {
            return;
        }

        axios({
            method: 'post',
            url: targetUrl,
            data: { _token: LA.token },
            headers: { Accept: 'application/json' },
        })
        .then(function () {
            self.refresh();
        })
        .catch(function () {});
    },
};
