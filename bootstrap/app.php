<?php

use App\Http\Middleware\CheckRoleMiddleware;
use App\DTOs\ResponseDTO;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
// use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi();

        $middleware->alias([
            'role' => CheckRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                $dto = new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $e->errors(),
                ], 422);

                return response()->json($dto->payload, $dto->code);
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                $dto = new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                    'errors' => [
                        'code' => 'UNAUTHENTICATED',
                    ],
                ], 401);

                return response()->json($dto->payload, $dto->code);
            }
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                $dto = new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Forbidden.',
                    'errors' => [
                        'code' => 'FORBIDDEN',
                    ],
                ], 403);

                return response()->json($dto->payload, $dto->code);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                $dto = new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan.',
                    'errors' => [
                        'code' => 'NOT_FOUND',
                    ],
                ], 404);

                return response()->json($dto->payload, $dto->code);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                $dto = new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Endpoint tidak ditemukan.',
                    'errors' => [
                        'code' => 'NOT_FOUND',
                    ],
                ], 404);

                return response()->json($dto->payload, $dto->code);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                $dto = new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Method not allowed.',
                    'errors' => [
                        'code' => 'METHOD_NOT_ALLOWED',
                    ],
                ], 405);

                return response()->json($dto->payload, $dto->code);
            }
        });

        // Catch-all: ensure API errors still match ResponseDTO shape.
        $exceptions->render(function (Throwable $e, $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            $statusCode = 500;
            if ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
            }

            $isDebug = (bool) (config('app.debug') ?? false);

            $dto = new ResponseDTO([
                'status' => 'error',
                'message' => $isDebug ? $e->getMessage() : 'Terjadi kesalahan pada server.',
                'errors' => $isDebug ? [
                    'exception' => get_class($e),
                    'detail' => $e->getMessage(),
                ] : [
                    'code' => 'INTERNAL_SERVER_ERROR',
                ],
            ], $statusCode);

            return response()->json($dto->payload, $dto->code);
        });
    })->create();
