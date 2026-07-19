<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IntegrationEventIngestionService;
use Illuminate\Http\Request;

class InternalIntegrationController extends Controller
{
    public function health(Request $request)
    {
        $client = $request->attributes->get('integration_client');

        return response()->json([
            'status' => 'ok',
            'client' => $client?->app_code,
            'time' => now()->toIso8601String(),
        ]);
    }

    public function events(Request $request, IntegrationEventIngestionService $ingestion)
    {
        $client = $request->attributes->get('integration_client');
        $result = $ingestion->ingest($request->all(), $client);

        return response()->json($result, $result['status'] === 'duplicate' ? 200 : 202);
    }
}
