<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable('user_id', 'name', 'tax_id', 'contact_email', 'contact_phone', 'commission_rate')]
class ManagementCompany extends Model
{
    protected function casts(): array
    {
        return ['commission_rate' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    /** Todas las incidencias de sus comunidades (aisladas de datos) */
    public function incidents(): HasManyThrough
    {
        return $this->hasManyThrough(Incident::class, Community::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
}
