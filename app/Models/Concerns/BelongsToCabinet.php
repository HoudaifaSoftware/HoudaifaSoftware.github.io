<?php

namespace App\Models\Concerns;

use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;

/**
 * Row-level multi-tenancy for cabinet-scoped models.
 *
 * A global scope restricts every query to the authenticated user's cabinet,
 * and the creating event auto-fills cabinet_id from that same user. The scope
 * is intentionally inert whenever there is no authenticated user with a
 * cabinet (console, queue, seeders) or when the current request is served by a
 * platform administrator / a Filament panel, so back-office staff can operate
 * across every cabinet. Use Model::withoutCabinetScope() to bypass explicitly.
 *
 * @phpstan-require-extends Model
 */
trait BelongsToCabinet
{
    /**
     * Ensure cabinet_id is mass-assignable regardless of whether the model
     * declares fillable via the #[Fillable] attribute or the $fillable
     * property, without touching each model's own declaration.
     */
    public function initializeBelongsToCabinet(): void
    {
        if (! in_array('cabinet_id', $this->getFillable(), true)) {
            $this->mergeFillable(['cabinet_id']);
        }
    }

    /**
     * Stable identifier for the tenant global scope, used so
     * withoutGlobalScope() can reliably remove it.
     */
    public const CABINET_SCOPE = 'cabinet';

    public static function bootBelongsToCabinet(): void
    {
        static::addGlobalScope(self::CABINET_SCOPE, new class implements Scope
        {
            public function apply(Builder $builder, Model $model): void
            {
                /** @var int|null $cabinetId */
                $cabinetId = $model::currentCabinetId();

                if ($cabinetId === null) {
                    return;
                }

                $builder->where($model->getTable().'.cabinet_id', $cabinetId);
            }
        });

        static::creating(static function (Model $model): void {
            if ($model->getAttribute('cabinet_id') !== null) {
                return;
            }

            /** @var int|null $cabinetId */
            $cabinetId = $model::currentCabinetId();

            if ($cabinetId !== null) {
                $model->setAttribute('cabinet_id', $cabinetId);
            }
        });
    }

    /**
     * Query scope that removes the tenant global scope for a single query.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutCabinetScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(self::CABINET_SCOPE);
    }

    /**
     * Fetch a fresh builder that ignores the tenant global scope.
     *
     * @return Builder<static>
     */
    public static function withoutCabinetScope(): Builder
    {
        return static::query()->withoutGlobalScope(self::CABINET_SCOPE);
    }

    /**
     * @return BelongsTo<Cabinet, $this>
     */
    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    /**
     * Resolve the cabinet the current query context should be constrained to,
     * or null when no tenant constraint applies.
     */
    public static function currentCabinetId(): ?int
    {
        if (! app()->bound('auth') || ! auth()->hasUser()) {
            return null;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        // Platform staff and the Filament back office operate cross-cabinet.
        if ($user->is_platform_admin || self::isPlatformContext()) {
            return null;
        }

        $cabinetId = $user->cabinet_id;

        return $cabinetId === null ? null : (int) $cabinetId;
    }

    private static function isPlatformContext(): bool
    {
        $request = request();

        if (! $request instanceof \Illuminate\Http\Request) {
            return false;
        }

        return $request->is('admin', 'admin/*');
    }
}
