<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class SlugService
{
    public function refreshSlugIfChanged(
        Model $model,
        string $watchedAttribute,
        string $newSlug
    ): void {
        if (! $model->wasChanged($watchedAttribute)) {
            return;
        }

        $model->slug = $newSlug;
        $model->save();
    }
}
