<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'incident_id', 'management_company_id', 'base_price', 'commission_rate', 'amount', 'finished_at'
])]
class Commission extends Model
{
    protected function casts(): array 
    {
        return [
            'base_price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'finished_at' => 'datetime',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function managementCompany(): BelongsTo
    {
        return $this->belongsTo(ManagementCompany::class);
    }
}
