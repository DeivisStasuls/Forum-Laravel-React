<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PrivateMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'private_group_id',
        'user_id',
        'body',
        'image_path',
    ];

    protected $appends = ['image_url'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PrivateGroup::class, 'private_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }
}
