<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAdvertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_position_id',
        'role',
        'position_level',
        'years_of_experience',
        'location',
        'is_draft',
    ];
}
