<?php


namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait ApiResponses {

    protected function ok($message, $data){
        return $this->success($message, $data, 200);
    }

    protected function success($message, $data, $statusCode = 200) {
        return response()->json([
            "data" => $data,
            "message" => $message,
            "status" => $statusCode,
        ], $statusCode);
    }

    protected function error($message, $statusCode, $reason = "") {

        Log::error("Unexpected error while {$message}: " . $reason);

        return response()->json([
            "message" => $message,
            "status" => $statusCode,
        ], $statusCode);
    }
}