<?php

namespace MenqzAdmin\Admin\Notifications\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MenqzAdmin\Admin\Auth\Database\Administrator;
use MenqzAdmin\Admin\Traits\DefaultDatetimeFormat;

class Notification extends Model
{
    use DefaultDatetimeFormat;

    protected $fillable = [
        'user_id',
        'role_id',
        'title',
        'description',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $this->setConnection($connection);
        $this->setTable(config('admin.database.notifications_table'));

        parent::__construct($attributes);
    }

    public function user(): BelongsTo
    {
        $relatedModel = config('admin.database.users_model', Administrator::class);

        return $this->belongsTo($relatedModel, 'user_id');
    }

    public function role(): BelongsTo
    {
        $relatedModel = config('admin.database.roles_model');

        return $this->belongsTo($relatedModel, 'role_id');
    }

    public function markViewed(): void
    {
        if ($this->viewed_at) {
            return;
        }

        $this->forceFill(['viewed_at' => now()])->save();
    }

    public function scopeVisibleTo(Builder $query, $user): Builder
    {
        $userId = (int) $user->getAuthIdentifier();
        $roleIds = method_exists($user, 'roles') ? $user->roles()->pluck('id')->all() : [];

        return $query->where(function (Builder $q) use ($userId, $roleIds) {
            $q->where('user_id', $userId);
            if (!empty($roleIds)) {
                $q->orWhereIn('role_id', $roleIds);
            }
        });
    }
}

