<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subforum extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'slug'];

    /**
     * A Subforum has many Threads.
     */
    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }

    /**
     * Generate a unique slug from the name
     */
    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
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