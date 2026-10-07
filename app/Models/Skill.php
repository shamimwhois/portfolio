<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;
    use HasPublishStatus;

    protected $fillable = ['name', 'level', 'icon', 'sort_order', 'publish_status'];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
    ];
}
