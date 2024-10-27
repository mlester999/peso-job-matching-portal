<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'job_advertisement_id',
        'birth_date',
        'sex',
        'province',
        'city',
        'barangay',
        'street_address',
        'zip_code',
        'education',
        'work_experience',
        'list_of_credentials',
        'skills',
        'status',
        'is_draft',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function jobAdvertisement(): BelongsTo
    {
        return $this->belongsTo(JobAdvertisement::class);
    }
}
