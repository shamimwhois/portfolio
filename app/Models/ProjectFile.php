<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFile extends Model
{
    use HasFactory;

    public const KIND_DELIVERABLE = 'deliverable';

    public const KIND_ATTACHMENT = 'attachment';

    protected $fillable = ['client_project_id', 'name', 'path', 'size', 'mime', 'kind'];

    protected $casts = [
        'size' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function isDeliverable(): bool
    {
        return $this->kind === self::KIND_DELIVERABLE;
    }

    public function isAttachment(): bool
    {
        return $this->kind === self::KIND_ATTACHMENT;
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
