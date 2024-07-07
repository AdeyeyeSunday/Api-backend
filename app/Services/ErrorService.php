<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ErrorService
{

    public function handleError(Exception $e): JsonResponse
    {
        Log::debug(json_encode($e->getMessage() . ' On Line:: ' . $e->getLine()));
        return response()->json([
            'message' => $e->getMessage() . ' On Line:: ' . $e->getLine(),
            'data' => null
        ], 400);
    }
}

