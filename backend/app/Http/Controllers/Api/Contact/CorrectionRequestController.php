<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Contact;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CorrectionRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'in:correction,accessibility,data_source,general'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'council_name' => ['nullable', 'string', 'max:120'],
            'council_slug' => ['nullable', 'string', 'max:120'],
            'page_url' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'string', 'max:255'],
            'details' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $requestModel = CorrectionRequest::create([
            'topic' => $data['topic'],
            'name' => trim($data['name']),
            'email' => Str::lower(trim($data['email'])),
            'council_name' => $this->nullableTrim($data['council_name'] ?? null),
            'council_slug' => $this->nullableTrim($data['council_slug'] ?? null),
            'page_url' => $this->nullableTrim($data['page_url'] ?? null),
            'source_url' => $this->nullableTrim($data['source_url'] ?? null),
            'details' => trim($data['details']),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Thanks. Your correction request has been received.',
            'request' => [
                'id' => $requestModel->id,
                'status' => $requestModel->status,
            ],
        ], 201);
    }

    private function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
