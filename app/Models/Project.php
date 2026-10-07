<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    use HasPublishStatus;

    protected $fillable = ['title', 'description', 'link', 'thumbnail', 'tech', 'status', 'publish_status', 'featured', 'sort_order'];

    protected $casts = [
        'tech' => 'array',
        'featured' => 'boolean',
        'sort_order' => 'integer',
    ];
}
