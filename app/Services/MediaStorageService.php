<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaStorageService
{
    public function storeFromRequest(Request $request, string $field, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store($directory, 'public');
    }

    public function resolveImagePathForUpdate(
        Request $request,
        string $field,
        string $directory,
        ?string $currentPath,
        string $removeFlag = 'remove_image'
    ): ?string {
        $nextPath = $currentPath;

        if ($request->boolean($removeFlag) && $nextPath) {
            $this->deletePublicFile($nextPath);
            $nextPath = null;
        }

        if ($request->hasFile($field)) {
            if ($nextPath) {
                $this->deletePublicFile($nextPath);
            }

            $nextPath = $request->file($field)->store($directory, 'public');
        }

        return $nextPath;
    }

    public function deletePublicFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
