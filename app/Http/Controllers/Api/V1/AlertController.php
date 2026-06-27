<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Http\Responses\ApiResponse;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * List alerts with optional filters.
     *
     * GET /api/v1/alerts?machine_id=&resolved=false&seen=false
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alert::query()
            ->with(['machine', 'metric'])
            ->orderByDesc('created_at');

        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->string('machine_id'));
        }

        if ($request->has('resolved')) {
            $resolved = filter_var($request->input('resolved'), FILTER_VALIDATE_BOOLEAN);
            $resolved ? $query->whereNotNull('resolved_at') : $query->whereNull('resolved_at');
        }

        if ($request->has('seen')) {
            $seen = filter_var($request->input('seen'), FILTER_VALIDATE_BOOLEAN);
            $seen ? $query->whereNotNull('seen_at') : $query->whereNull('seen_at');
        }

        $alerts = $query->paginate(20);

        return ApiResponse::success(AlertResource::collection($alerts));
    }

    /**
     * Mark alert as seen.
     *
     * POST /api/v1/alerts/{id}/seen
     */
    public function seen(Alert $alert): JsonResponse
    {
        $alert->update(['seen_at' => now()->utc()]);
        $alert->load(['machine', 'metric']);

        return ApiResponse::success(new AlertResource($alert), 'Alert marked as seen');
    }

    /**
     * Mark alert as resolved.
     *
     * POST /api/v1/alerts/{id}/resolve
     */
    public function resolve(Alert $alert): JsonResponse
    {
        $alert->update(['resolved_at' => now()->utc()]);
        $alert->load(['machine', 'metric']);

        return ApiResponse::success(new AlertResource($alert), 'Alert resolved');
    }
}
