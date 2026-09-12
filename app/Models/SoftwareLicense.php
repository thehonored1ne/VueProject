<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingCycle;
use Database\Factories\SoftwareLicenseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareLicense extends Model
{
    /** @use HasFactory<SoftwareLicenseFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'vendor',
        'license_key',
        'seats_total',
        'cost_per_seat',
        'billing_cycle',
        'expires_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seats_total' => 'integer',
            'cost_per_seat' => 'decimal:2',
            'billing_cycle' => BillingCycle::class,
            'expires_at' => 'date',
        ];
    }

    /**
     * Seat assignments for this license.
     *
     * @return HasMany<LicenseAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LicenseAssignment::class)->orderByDesc('assigned_at');
    }

    /**
     * Employees assigned to this license.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'license_assignments')
            ->withPivot(['assigned_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Scope query to search by name or vendor.
     *
     * @param  Builder<SoftwareLicense>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $q, string $term) {
            $q->where(function (Builder $sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('vendor', 'like', "%{$term}%");
            });
        });
    }

    /**
     * Scope query by vendor.
     *
     * @param  Builder<SoftwareLicense>  $query
     */
    public function scopeFilterVendor(Builder $query, ?string $vendor): Builder
    {
        return $query->when($vendor, fn (Builder $q) => $q->where('vendor', $vendor));
    }
}
