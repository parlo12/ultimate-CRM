<?php

namespace App\Models;

use App\Library\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static firstOrNew(array $array)
 * @method static truncate()
 * @method static insert(array[] $freePlanCreditPricing)
 * @method static where(string $string, string $string1, float|int|string $amount)
 */
class PlanSendingCreditPrice extends Model
{
    use HasUid;

    protected $fillable = [
        'plan_id',
        'unit_from',
        'unit_to',
        'per_credit_cost',
    ];

    /**
     * Country
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function calculateUnits()
    {
        $unitsFrom = floor($this->unit_from / $this->per_credit_cost);
        $unitsTo = floor($this->unit_to / $this->per_credit_cost);

        return "$unitsFrom - $unitsTo ".__('locale.labels.units');
    }
}
