<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasUuid;

    protected $fillable = [
        'feature_key',
        'label',
        'description',
        'enabled_for_roles',
        'enabled_for_users',
        'is_globally_enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled_for_roles' => 'array',
            'enabled_for_users' => 'array',
            'is_globally_enabled' => 'boolean',
        ];
    }

    public function isEnabledFor(User $user): bool
    {
        if ($this->is_globally_enabled) {
            return true;
        }

        if (in_array($user->id, $this->enabled_for_users ?? [])) {
            return true;
        }

        $userRoles = $user->getRoleNames()->toArray();
        return !empty(array_intersect($userRoles, $this->enabled_for_roles ?? []));
    }
}
