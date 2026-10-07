<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelinePhase extends Model
{
    use HasFactory;
    use HasPublishStatus;

    protected $fillable = ['days', 'title', 'description', 'sort_order', 'publish_status'];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
