<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use App\Helpers\ApiResponse;

class HandleRoleAuthorizationException
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (UnauthorizedException $e) {
            return ApiResponse::Error("Anda tidak memiliki role yang sesuai untuk mengakses resource ini", 403);
        }
    }
}
