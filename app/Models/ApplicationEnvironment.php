<?php

namespace App\Models;

use App\Models\Traits\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationEnvironment extends Model
{
    use HasFactory;


    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withTimestamps();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'application_environment_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(\Spatie\Permission\Models\Permission::class, 'application_environment_id');
    }

//    /**
//     * Scope to get only active, non-expired environments
//     */
//    public function scopeActive($query)
//    {
//        return $query->where('is_active', true)
//                    ->where(function ($q) {
//                        $q->where('is_temporary', false)
//                          ->orWhere(function ($temp) {
//                              $temp->where('is_temporary', true)
//                                   ->where(function ($exp) {
//                                       $exp->whereNull('expires_at')
//                                           ->orWhere('expires_at', '>', now());
//                                   });
//                          });
//                    });
//    }

    /**
     * Get the full environment identifier (application.environment)
     */
    public function getFullIdentifierAttribute(): string
    {
        return $this->application->slug . '.' . $this->environment;
    }
}
