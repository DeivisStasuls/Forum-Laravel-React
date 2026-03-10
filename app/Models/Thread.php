<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Thread extends Model
{
    use HasFactory;

    /**
     * Generate a unique slug from the title
     */
    use HasSlug;

    protected $fillable = [
        'title',
        'body',
        'creator_only_comments',
        'slug',
        'user_id',
        'subforum_id',
        'edited_by_user_id',
        'edited_at',
    ];

    protected $casts = [
        'creator_only_comments' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subforum(): BelongsTo
    {
        return $this->belongsTo(Subforum::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }

    /**
     * Boot method to auto-generate slug if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($thread) {
            if (empty($thread->slug)) {
                $thread->slug = static::generateSlug($thread->title);
            }
        });
    }
    public static function findThread(string $slug, bool $withRelations = false): Thread
{
    $query = static::where('slug', $slug);

    if ($withRelations) {
        $query->with(['user', 'subforum', 'posts.user'])
              ->withCount('posts');
    }

    return $query->firstOrFail();
}

}