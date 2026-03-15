<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subforum extends Model

{
    use HasFactory;


      /**
     * Generate a unique slug from the title
     */
    use HasSlug;


    protected $fillable = ['name', 'description', 'restricted_thread_creation', 'slug'];

    protected $casts = [
        'restricted_thread_creation' => 'boolean',
    ];

    /**
     * A Subforum has many Threads.
     */
    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }

    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subforum_moderators')
            ->withTimestamps();
    }

    /**
     * Boot method to auto-generate slug if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subforum) {
            if (empty($subforum->slug)) {
                $subforum->slug = static::generateSlug($subforum->name);
            }
        });
    }
}