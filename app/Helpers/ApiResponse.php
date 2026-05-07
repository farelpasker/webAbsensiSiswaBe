<?php

namespace App\Helpers;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function Success($data = null, $message, $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function Error($message = 'Error', $code = 500, $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function ServerError(string $message = 'Terjadi kesalahan pada server.'): JsonResponse
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            "code" => 500,
            "data" => null
        ])->setStatusCode(500);
    }

    public static function Notfound(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            "code" => 404,
            "data" => null
        ])->setStatusCode(404);
    }

    public static function Custom(bool $status, string $message, mixed $data, int $code): JsonResponse
    {
        return response()->json([
            "success" => $status,
            "message" => $message,
            "code" => $code,
            "data" => $data
        ])->setStatusCode($code);
    }

    public static function Paginate(mixed $data, string $message, mixed $paginate): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'code' => 200,
            'paginate' => $paginate
        ])->setStatusCode(200);
    }

    public static function Create(string $message, mixed $data): JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => $message,
            "code" => 201,
            "data" => $data
        ])->setStatusCode(201);
    }

    public static function Forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            "code" => 403,
            "data" => null
        ])->setStatusCode(403);
    }
}