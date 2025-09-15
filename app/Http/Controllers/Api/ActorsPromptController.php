<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActorExtractionService;
use Illuminate\Http\JsonResponse;

class ActorsPromptController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => ActorExtractionService::getPromptText()
        ]);
    }
}
