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
        this._items = document.getElementById('menqz-admin-notification-items');
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

        document.addEventListener('click', function (event) {
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

            const item = event.target.closest('[data-menqz-admin-notification-id]');
            if (item) {
                event.preventDefault();
                const id = item.dataset.menqzAdminNotificationId;
                self.read(id);
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

        if (!this._items) {
            return;
        }

        this._items.innerHTML = '';

        if (!notifications.length) {
            const empty = document.createElement('div');
            empty.className = 'px-2 py-2 text-center text-muted';
            empty.textContent = 'Sem notificações';
            this._items.appendChild(empty);
            return;
        }

        notifications.forEach((n) => {
            const a = document.createElement('a');
            a.href = '#';
            a.className = 'dropdown-item d-block rounded';
            a.dataset.menqzAdminNotificationId = n.id;

            const title = document.createElement('div');
            title.className = 'fw-semibold';
            title.textContent = n.title || '';

            const desc = document.createElement('div');
            desc.className = 'small text-muted';
            desc.textContent = n.description || '';

            a.appendChild(title);
            a.appendChild(desc);
            this._items.appendChild(a);
        });
    },

    getReadUrl: function (id) {
        return (this._config.readUrlTemplate || '').replace('__ID__', String(id));
    },

    read: function (id) {
        const url = this.getReadUrl(id);
        return this.readByUrl(url);
    },

    readByUrl: function (url) {
        const self = this;
        if (!url) {
            return;
        }

        axios({
            method: 'post',
            url: url,
            data: { _token: LA.token },
            headers: { Accept: 'application/json' },
        })
        .then(function () {
            self.refresh();
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
