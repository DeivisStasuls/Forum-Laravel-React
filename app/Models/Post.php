<?php

namespace App\Models;

use App\Traits\Votable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;


class Post extends Model
{
    use HasFactory;
    
    use Votable;

    protected $fillable = [
        'body',
        'image_path',
        'user_id',
        'thread_id',
        'edited_by_user_id',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    protected $appends = ['score', 'user_vote', 'image_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }
}

