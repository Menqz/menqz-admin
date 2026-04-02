<!-- Main Header -->
<header class="custom-navbar navbar navbar-light bg-white p-0 align-items-stretch">
    <a class="navbar-header navbar-brand menu-width container-md bg-semi-dark text-center" href="{{ admin_url('/') }}">
        <span class="short">{!! config('admin.logo-mini', config('admin.name')) !!}</span><span class="long">{!! config('admin.logo', config('admin.name')) !!}</span>
    </a>
    <div class="d-flex flex-fill flex-wrap header-items">

        <a class="flex-shrink order-1 order-sm-0 valign-header px-4 link-secondary" type="button" id='menu-toggle' aria-controls="menu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="icon-bars"></i>
        </a>

        <ul class="nav navbar-nav hidden-sm visible-lg-block">
            {!! Admin::getNavbar()->render('left') !!}
        </ul>

        <div class="flex-fill search order-0 order-sm-1" style="display:none;">
            <input class="form-control" type="text" placeholder="Search" aria-label="Search">
        </div>

        <ul class="nav order-2 ms-auto d-flex align-items-center me-2">

            {!! Admin::getNavbar()->render() !!}

            @if(config('admin.notifications.enabled'))
                <li class="nav-item dropdown me-2">
                    <div
                        class="dropdown d-flex align-items-center"
                        role="button"
                        id="menqz-admin-notification-link"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
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
                            <i class="icon-bell position-relative" style="font-size: 1.5rem;">
                                <span id="menqz-admin-notification-badge" style="font-size: 0.75rem;"
                                    class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger d-none d-flex align-items-center justify-content-center">
                                    <span class="visually-hidden">notificações não lidas</span>
                                </span>
                            </i>
                        </span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menqz-admin-notification-link" style="min-width: 22rem; line-height: normal;">
                        <li class="px-3 py-0 d-flex align-items-center justify-content-between">
                            <span class="fw-semibold">Notificações</span>
                            <button type="button" class="btn btn-secondary btn-sm" id="menqz-admin-notification-read-all-header">Marcar todas</button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li id="menqz-admin-notification-items" class="px-2"></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 py-2">
                            <a href="{{ admin_url('notifications') }}" class="btn btn-secondary w-100">Ver todas</a>
                        </li>
                    </ul>
                </li>
            @endif

            <li class="nav-item">
                <div class="dropdown user-menu d-flex align-items-center p3-3" href="#" role="button" id="user-menu-link" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="bg-light inline rounded-circle user-image">
                        <img src="{{ Admin::user()->getUrlAvatar() }}" alt="User Image">
                    </span>
                    <span class="hidden-xs">{{ Admin::user()->name }}</span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="user-menu-link">
                    <!-- The user image in the menu -->
                    <li class="user-header text-center bg-semi-dark px-3">
                        <span class="bg-light inline rounded-circle user-image medium">
                            <img src="{{ Admin::user()->getUrlAvatar() }}" alt="User Image">
                        </span>
                        <p>
                            <h2>{{ Admin::user()->name }}</h2>
                            <small>Member since admin {{ Admin::user()->created_at }}</small>
                        </p>
                    </li>
                    <li class="user-footer p-2 clearfix">
                        <div class="float-start">
                            <a href="{{ admin_url('auth/setting') }}" class="btn btn-secondary">{{ __('admin.setting') }}</a>
                        </div>
                        <div class="float-end">
                            <a href="{{ admin_url('auth/logout') }}" class="btn no-ajax btn-secondary">{{ __('admin.logout') }}</a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>
