<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAdvertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_position_id',
        'employer_id',
        'role',
        'skills',
        'position_level',
        'years_of_experience',
        'location',
        'industry',
        'is_draft',
        'is_active',
    ];

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
