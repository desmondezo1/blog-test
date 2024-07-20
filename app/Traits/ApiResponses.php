<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function ok(string $message, mixed $data): JsonResponse
    {
        return $this->success($message, $data, 200);
    }

    protected function success(string $message, mixed $data, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            "data" => $data,
            "message" => $message,
            "status" => $statusCode,
        ], $statusCode);
    }

    protected function error(string $message, int $statusCode, string $reason = ""): JsonResponse
    {
        Log::error("Unexpected error while {$message}: " . $reason);

        return response()->json([
            "message" => $message,
            "status" => $statusCode,
        ], $statusCode);
    }
}