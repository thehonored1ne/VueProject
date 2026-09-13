<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Asset assignments held by this user.
     *
     * @return HasMany<AssetAssignment, $this>
     */
    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    /**
     * Active (unreturned) asset assignments held by this user.
     *
     * @return HasMany<AssetAssignment, $this>
     */
    public function activeAssetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->whereNull('returned_at');
    }

    /**
     * Software license seats held by this user.
     *
     * @return HasMany<LicenseAssignment, $this>
     */
    public function licenseAssignments(): HasMany
    {
        return $this->hasMany(LicenseAssignment::class);
    }

    /**
     * Software licenses allocated to this user.
     *
     * @return BelongsToMany<SoftwareLicense, $this>
     */
    public function softwareLicenses(): BelongsToMany
    {
        return $this->belongsToMany(SoftwareLicense::class, 'license_assignments')
            ->withPivot(['assigned_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Scope query to search users by name or email.
     *
     * @param  Builder<User>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $q, string $term) {
            $q->where(function (Builder $sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        });
    }

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
}
