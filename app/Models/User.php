<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function systemAdminship(): HasOne {
        return $this->hasOne(SystemAdmin::class);
    }

    public function teamAdminships(): BelongsToMany {
        return $this->belongsToMany(Team::class, 'team_admins');
    }

    public function teamMemberships(): BelongsToMany {
        return $this->belongsToMany(Team::class, 'team_members');
    }

    public function partUploads(): HasMany {
        return $this->hasMany(PartUpload::class);
    }

    public function teams(): Attribute {
        return Attribute::make(
            get: function () {
                return $this->teamAdminships->merge($this->teamMemberships);
            },
        );
    }
}
