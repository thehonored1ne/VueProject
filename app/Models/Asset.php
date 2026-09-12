<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_tag',
        'name',
        'type',
        'status',
        'serial_number',
        'model_number',
        'cost',
        'purchased_at',
        'warranty_expires_at',
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
            'type' => AssetType::class,
            'status' => AssetStatus::class,
            'cost' => 'decimal:2',
            'purchased_at' => 'date',
            'warranty_expires_at' => 'date',
        ];
    }

    /**
     * All custody assignment history for this asset.
     *
     * @return HasMany<AssetAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->orderByDesc('assigned_at');
    }

    /**
     * The active assignment if currently checked out.
     *
     * @return HasOne<AssetAssignment, $this>
     */
    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_at');
    }

    /**
     * All maintenance and repair history for this asset.
     *
     * @return HasMany<AssetMaintenance, $this>
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->orderByDesc('created_at');
    }

    /**
     * The active repair job if currently scheduled or in progress.
     *
     * @return HasOne<AssetMaintenance, $this>
     */
    public function currentMaintenance(): HasOne
    {
        return $this->hasOne(AssetMaintenance::class)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->latestOfMany();
    }

    /**
     * Scope query to search by asset tag, name, or serial number.
     *
     * @param  Builder<Asset>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $q, string $term) {
            $q->where(function (Builder $sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('asset_tag', 'like', "%{$term}%")
                    ->orWhere('serial_number', 'like', "%{$term}%")
                    ->orWhere('model_number', 'like', "%{$term}%");
            });
        });
    }

    /**
     * Scope query by status.
     *
     * @param  Builder<Asset>  $query
     */
    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $q) => $q->where('status', $status));
    }

    /**
     * Scope query by asset type.
     *
     * @param  Builder<Asset>  $query
     */
    public function scopeFilterType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn (Builder $q) => $q->where('type', $type));
    }
}
