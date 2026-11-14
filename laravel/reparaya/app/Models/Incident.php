<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentStatus;
use App\Enums\Urgency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable('speciality_id', 'community_id', 'description', 'address', 'service_date', 'urgency')]
class Incident extends Model
{
    protected function casts(): array
    {
        return [
            'service_date' => 'datetime',
            'finished_at' => 'datetime',
            'status' => IncidentStatus::class,
            'urgency' => Urgency::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /** Historial ordenado cronológicamente */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(IncidentStatusLog::class)->orderBy('created_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IncidentComment::class)->orderBy('created_at');
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }
}
