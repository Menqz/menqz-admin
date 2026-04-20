<?php

namespace MenqzAdmin\Admin\Auth;

use Illuminate\Support\Carbon;

class PasswordPolicy
{
    public static function passwordRules(bool $required = true): array
    {
        $rules = [
            $required ? 'required' : 'nullable',
            'string',
            'confirmed',
            'min:'.self::minimumLength(),
        ];

        $requirements = self::strengthRequirements();

        $rules[] = static function ($attribute, $value, $fail) use ($requirements) {
            if (empty($value)) {
                return;
            }

            $missing = [];

            if (!empty($requirements['letter']) && !preg_match('/[A-Za-z]/', $value)) {
                $missing[] = trans('admin.password_requirement_letter');
            }

            if (!empty($requirements['uppercase']) && !preg_match('/[A-Z]/', $value)) {
                $missing[] = trans('admin.password_requirement_uppercase');
            }

            if (!empty($requirements['number']) && !preg_match('/\d/', $value)) {
                $missing[] = trans('admin.password_requirement_number');
            }

            if (!empty($requirements['symbol']) && !preg_match('/[^A-Za-z0-9]/', $value)) {
                $missing[] = trans('admin.password_requirement_symbol');
            }

            if (!empty($missing)) {
                $fail(trans('admin.password_strength_failed', [
                    'requirements' => implode(', ', $missing),
                ]));
            }
        };

        return $rules;
    }

    public static function shouldForceChange($user): bool
    {
        if (!$user) {
            return false;
        }

        if (self::temporaryPasswordEnabled() && (bool) $user->getAttribute('is_temporary_password')) {
            return true;
        }

        if (!self::forceRenewalEnabled()) {
            return false;
        }

        $renewalDays = self::renewalDays();

        if ($renewalDays <= 0) {
            return false;
        }

        $changedAt = $user->getAttribute('password_changed_at');

        if (empty($changedAt)) {
            return true;
        }

        try {
            return Carbon::parse($changedAt)->addDays($renewalDays)->isPast();
        } catch (\Throwable $e) {
            return true;
        }
    }

    public static function forceRenewalEnabled(): bool
    {
        return (bool) config('admin.auth.password_policy.force_renewal.enabled', false);
    }

    public static function renewalDays(): int
    {
        return max((int) config('admin.auth.password_policy.force_renewal.days', 90), 0);
    }

    public static function temporaryPasswordEnabled(): bool
    {
        return (bool) config('admin.auth.password_policy.temporary_password.enabled', true);
    }

    protected static function minimumLength(): int
    {
        return max((int) config('admin.auth.password_policy.strength.min', 8), 1);
    }

    protected static function strengthRequirements(): array
    {
        return array_merge([
            'letter' => true,
            'uppercase' => false,
            'number' => false,
            'symbol' => false,
        ], (array) config('admin.auth.password_policy.strength.require', []));
    }
}
