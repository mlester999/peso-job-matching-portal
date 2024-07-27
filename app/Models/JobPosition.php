<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'skills',
        'is_active',
    ];

    public function jobAdvertisement(): HasOne
    {
        return $this->hasOne(JobAdvertisement::class);
    }
}
