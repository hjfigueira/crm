<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Facades\Filament;

/**
 * @property int $id
 * @property string $type
 * @property string $description
 * @property string|null $category
 * @property string $amount
 * @property string $due_date
 * @property string|null $payment_date
 * @property string $status
 * @property int|null $cost_center_id
 * @property int|null $project_id
 * @property int $tenant_id
 */
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'description',
        'category',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'cost_center_id',
        'project_id',
        'tenant_id',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public const TYPE_PAYABLE = 'payable';
    public const TYPE_RECEIVABLE = 'receivable';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';

    /**
     * @return BelongsTo<CostCenter, Transaction>
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * @return BelongsTo<Project, Transaction>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<Tenant, Transaction>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted(): void
    {
        // Apply tenant global scope based on Filament active tenant
        static::addGlobalScope('tenant', function (Builder $query): void {
            $tenant = Filament::getTenant();
            if ($tenant) {
                $query->where('tenant_id', $tenant->getKey());
            }
        });

        // Auto-derive status on saving
        static::saving(function (self $model): void {
            if ($model->status !== self::STATUS_PAID) {
                $today = Carbon::today();
                if ($model->payment_date !== null) {
                    $model->status = self::STATUS_PAID;
                } elseif ($model->due_date !== null && Carbon::parse($model->due_date)->lt($today)) {
                    $model->status = self::STATUS_OVERDUE;
                } else {
                    $model->status = self::STATUS_PENDING;
                }
            }
        });
    }

    public function markAsPaid(?string $paymentDate = null): void
    {
        $this->payment_date = $paymentDate ? Carbon::parse($paymentDate) : Carbon::today();
        $this->status = self::STATUS_PAID;
        $this->save();
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->where('status', self::STATUS_OVERDUE)
              ->orWhere(function (Builder $q2): void {
                  $q2->whereDate('due_date', '<', Carbon::today())
                     ->whereNull('payment_date');
              });
        });
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if ($this->payment_date !== null || $this->due_date === null) {
            return null;
        }

        $due = Carbon::parse($this->due_date);
        $days = $due->diffInDays(Carbon::today(), false);

        return $days > 0 ? $days : null;
    }
}
