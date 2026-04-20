<li class="nav-item me-2">
    <button
        type="button"
        class="menqz-admin-notification-trigger d-flex align-items-center"
        id="menqz-admin-notification-link"
        data-bs-toggle="offcanvas"
        data-bs-target="#menqz-admin-notification-panel"
        aria-controls="menqz-admin-notification-panel"
        data-unread-url="{{ admin_url('notifications/unread') }}"
        data-read-url-template="{{ admin_url('notifications/__ID__/read') }}"
        data-read-all-url="{{ admin_url('notifications/read-all') }}"
        data-index-url="{{ admin_url('notifications') }}"
        data-broadcast-auth-url="{{ admin_url('broadcasting/auth') }}"
        data-user-id="{{ Admin::user()->getAuthIdentifier() }}"
        data-role-ids='@json(Admin::user()->roles()->pluck("id")->values())'
        data-pusher-enabled="{{ config('admin.notifications.pusher.enabled') ? 1 : 0 }}"
        data-pusher-key="{{ (string) config('admin.notifications.pusher.key') }}"
        data-pusher-cluster="{{ (string) config('admin.notifications.pusher.cluster') }}"
        data-pusher-force-tls="{{ config('admin.notifications.pusher.force_tls') ? 1 : 0 }}"
    >
        <span class="valign-header px-3 link-secondary position-relative">
            <i class="icon-bell position-relative">
                <span id="menqz-admin-notification-badge"
                    class="menqz-admin-notification-badge position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger d-none d-flex align-items-center justify-content-center">
                    <span class="visually-hidden">notificações não lidas</span>
                </span>
            </i>
        </span>
    </button>

    <div class="offcanvas offcanvas-end menqz-admin-notification-canvas" tabindex="-1" id="menqz-admin-notification-panel" aria-labelledby="menqz-admin-notification-title">
        <div class="offcanvas-header align-items-start">
            <div class="menqz-admin-notification-heading">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="offcanvas-title mb-0" id="menqz-admin-notification-title">Notificações</h5>
                    <span class="menqz-admin-notification-count" id="menqz-admin-notification-count">0</span>
                </div>
                <p class="text-muted small mb-0" id="menqz-admin-notification-summary">Acompanhe as atualizações mais recentes.</p>
            </div>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="menqz-admin-notification-toolbar">
            <button type="button" class="btn btn-link p-0" id="menqz-admin-notification-read-all-header">Marcar todas como lidas</button>
            <a href="{{ admin_url('notifications') }}" class="btn btn-link p-0">Ver todas</a>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <div class="menqz-admin-notification-list" id="menqz-admin-notification-items"></div>
            <div class="menqz-admin-notification-footer">

            </div>
        </div>
    </div>
</li>
