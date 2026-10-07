<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUpdate extends Model
{
    use HasFactory;

    public const TYPE_UPDATE = 'update';

    public const TYPE_MILESTONE = 'milestone';

    public const TYPE_NOTE = 'note';

    public const TYPE_REMINDER = 'reminder';

    protected $fillable = ['client_project_id', 'user_id', 'type', 'message'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
