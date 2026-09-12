<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaintenanceStatus;
use Database\Factories\AssetMaintenanceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AssetMaintenance extends Model
{
    /** @use HasFactory<AssetMaintenanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_id',
        'user_id',
        'title',
        'provider',
        'cost',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
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
            'status' => MaintenanceStatus::class,
            'cost' => 'decimal:2',
            'scheduled_at' => 'date',
            'started_at' => 'date',
            'completed_at' => 'date',
        ];
    }

    /**
     * The asset associated with this repair ticket.
     *
     * @return BelongsTo<Asset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * The IT staff member who logged or oversees the repair.
     *
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope query to active maintenance jobs.
     *
     * @param  Builder<AssetMaintenance>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            MaintenanceStatus::Scheduled->value,
            MaintenanceStatus::InProgress->value,
        ]);
    }
}
