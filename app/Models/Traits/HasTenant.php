<?php

namespace App\Models\Traits;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasTenant
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function bootHasTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $builder->tenant();
        });

        static::creating(function ($model) {
            if (! $model->account_id && Auth::user()?->account_id) {
                $model->account_id = Auth::user()->account_id;
            }
        });
    }

    public function scopeTenant($query)
    {
        if (Auth::user()?->account_id) {
            $query->whereIn('account_id', Auth::user()->account
                ->sourceAccounts
                ->pluck('id')
                ->push(Auth::user()->account_id)
                ->unique()
                ->values()
            );
        }
    }

    public function canBeViewedBy(User $user)
    {
        if ($user->isViewingAllRecords()) {
            return true;
        }
        if ($user->account_id === $this->account_id) {
            return true;
        }
        if ($user->account->sourceAccounts->pluck('id')->contains($this->account_id)) {
            return true;
        }

        return false;
    }

    public function scopeWithoutTenantConstraint($query)
    {
        $query->withoutGlobalScope('tenant');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withTrashed();
    }
}
