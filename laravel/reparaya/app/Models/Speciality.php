<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'responsibilities', 'base_salary', 'base_price'])]
class Speciality extends Model
{
    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'base_price' => 'decimal:2',
        ];
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(Technician::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}
