<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}