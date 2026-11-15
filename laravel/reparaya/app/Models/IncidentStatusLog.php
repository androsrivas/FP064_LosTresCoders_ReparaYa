<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('incident_id', 'user_id', 'previous_status', 'new_status', 'note')]
class IncidentStatusLog extends Model
{
    /** La tabla solo tiene created_at: registro inmutable */
    public const UPDATED_AT = null;

    protected function casts(): array 
    {
        return [
            'previous_status' => IncidentStatus::class,
            'new_status' => IncidentStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
