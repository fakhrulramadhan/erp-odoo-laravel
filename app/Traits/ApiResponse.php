<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(
        string $message = 'Error',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function created(
        mixed $data = null,
        string $message = 'Created successfully'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    protected function deleted(
        string $message = 'Deleted successfully'
    ): JsonResponse {
        return $this->success(null, $message, 200);
    }

    protected function paginated(ResourceCollection|LengthAwarePaginator $collection): JsonResponse
    {
        if ($collection instanceof ResourceCollection) {
            return response()->json([
                'success' => true,
                'data' => $collection->response()->getData(true),
            ]);
        }

        // LengthAwarePaginator
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $collection->items(),
                'meta' => [
                    'current_page' => $collection->currentPage(),
                    'last_page' => $collection->lastPage(),
                    'per_page' => $collection->perPage(),
                    'total' => $collection->total(),
                ],
            ],
        ]);
    }
}
