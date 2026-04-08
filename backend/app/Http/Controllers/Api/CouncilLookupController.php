<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Council;
use Illuminate\Http\JsonResponse;

class CouncilLookupController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $slug = trim($slug);

        $council = Council::query()
            ->with(['versions' => fn ($query) => $query->orderByDesc('valid_from')->orderByDesc('created_at')])
            ->where('canonical_slug', $slug)
            ->first();

        if ($council === null) {
            return response()->json([
                'message' => 'Council not found.',
            ], 404);
        }

        $version = $council->versions->first();

        return response()->json([
            'local_authority' => [
                'name' => $version?->display_name ?? $council->canonical_slug,
                'homepage_url' => null,
                'tier' => null,
                'slug' => $council->canonical_slug,
                'parent' => null,
                'authority_kind' => $council->authority_kind,
                'jurisdiction_code' => $council->jurisdiction_code,
                'country_code' => $council->country_code,
            ],
        ]);
    }
}
