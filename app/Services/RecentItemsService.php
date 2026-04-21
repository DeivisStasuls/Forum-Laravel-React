<?php

namespace App\Services;

use Illuminate\Http\Request;

class RecentItemsService
{
    public function remember(
        Request $request,
        string $sessionKey,
        array $item,
        int $itemId,
        int $limit = 5
    ): void {
        $existing = collect($request->session()->get($sessionKey, []))
            ->reject(fn ($entry) => (int) ($entry['id'] ?? 0) === $itemId)
            ->values();

        $updated = $existing
            ->prepend($item)
            ->take($limit)
            ->values()
            ->all();

        $request->session()->put($sessionKey, $updated);
    }
}
