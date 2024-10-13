<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    public function admins(): BelongsToMany {
        return $this->belongsToMany(User::class, 'team_admins');
    }

    public function members(): BelongsToMany {
        return $this->belongsToMany(User::class, 'team_members');
    }

    public function parts(): BelongsToMany {
        return $this->belongsToMany(Part::class, 'team_parts')
            ->withPivot(['multiplier', 'static_price', 'team_price'])
            ->withTimestamps();
    }
}