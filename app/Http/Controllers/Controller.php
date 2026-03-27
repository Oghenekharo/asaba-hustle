<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected function successResponse(
        mixed $data = null,
        string $message = 'Request completed successfully.',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        [$normalizedData, $resourceMeta] = $this->normalizeResponseData($data);

        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $normalizedData,
        ];

        $meta = array_filter(
            array_merge($resourceMeta, $meta),
            fn ($value) => $value !== null && $value !== []
        );

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function errorResponse(
        string $message,
        int $status,
        array $errors = [],
        array $meta = []
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function normalizeResponseData(mixed $data): array
    {
        if ($data instanceof JsonResource || $data instanceof AnonymousResourceCollection) {
            $resolved = $data->response()->getData(true);

            if (array_key_exists('data', $resolved)) {
                $meta = [];

                if (isset($resolved['links'])) {
                    $meta['links'] = $resolved['links'];
                }

                if (isset($resolved['meta'])) {
                    $meta['pagination'] = $resolved['meta'];
                }

                return [$resolved['data'], $meta];
            }

            return [$resolved, []];
        }

        return [$data, []];
    }
}
