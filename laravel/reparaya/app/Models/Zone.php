<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name'])]
class Zone extends Model
{
    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    /** zone -> communities -> incidents (base de la API por zonas) */
    public function incidents(): HasManyThrough
    {
        return $this->hasManyThrough(Incident::class, Community::class);
    }
}
