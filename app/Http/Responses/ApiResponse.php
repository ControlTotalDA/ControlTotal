<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        ?array $meta = null,
        int $status = 200
    ): JsonResponse {
        $payload = [
            'success' => true,
            'data' => self::resolveData($data),
            'message' => $message,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        } elseif ($data instanceof ResourceCollection && $data->resource instanceof AbstractPaginator) {
            $paginator = $data->resource;
            $payload['data'] = $data->resolve();
            $payload['meta'] = [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];
        } elseif ($data instanceof AbstractPaginator) {
            $payload['data'] = $data->items();
            $payload['meta'] = [
                'page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];
        }

        return response()->json($payload, $status);
    }

    public static function error(
        string $message,
        ?array $errors = null,
        int $status = 400
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    private static function resolveData(mixed $data): mixed
    {
        if ($data instanceof JsonResource) {
            return $data->resolve();
        }

        if ($data instanceof ResourceCollection) {
            return $data->resolve();
        }

        if ($data instanceof AbstractPaginator) {
            return $data->items();
        }

        return $data;
    }
}
