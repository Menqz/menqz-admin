<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>Notificações</div>
        <button type="button" class="btn btn-secondary btn-sm" id="menqz-admin-notification-read-all" data-read-all-url="{{ admin_url('notifications/read-all') }}">Marcar todas como lidas</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th class="text-nowrap">Criado em</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        <tr class="{{ $notification->viewed_at ? '' : 'table-warning' }}">
                            <td class="fw-semibold">{{ $notification->title }}</td>
                            <td>{{ $notification->description }}</td>
                            <td class="text-nowrap">{{ $notification->created_at }}</td>
                            <td class="text-nowrap">{{ $notification->viewed_at ? 'Lida' : 'Não lida' }}</td>
                            <td class="text-nowrap text-end">
                                @if(!$notification->viewed_at)
                                    <button type="button" class="btn btn-secondary btn-sm menqz-admin-notification-read" data-id="{{ $notification->id }}" data-read-url="{{ admin_url('notifications/'.$notification->id.'/read') }}">Marcar como lida</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">Sem notificações</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {!! $notifications->links() !!}
</div>

