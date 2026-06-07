<?php

namespace App\Http\Controllers;

use App\DTOs\ResponseDTO;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="API Documentation",
 * description="Swagger OpenApi description",
 * @OA\Contact(
 * email="developer@example.com"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="Main API Server"
 * )
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function respond(ResponseDTO $dto): JsonResponse
    {
        $payload = [
            'status' => $dto->status,
            'message' => $dto->message,
        ];

        if ($dto->status === 'success') {
            $payload['data'] = $dto->data ?? (object) [];
        } else {
            $payload['error'] = $dto->error ?? (object) [];
        }

        return response()->json($payload, $dto->code);
    }
}
