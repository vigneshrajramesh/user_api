<?php
namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\JWTException;

class CustomJwtMiddleware extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        Log::info('CustomJwtMiddleware: Handling request');
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (UnauthorizedHttpException $e) {
            return $this->handleUnauthorizedException($e);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization Token not found',
                'data' => []
            ], 401);
        }

        return $next($request);
    }

    private function handleUnauthorizedException(UnauthorizedHttpException $e)
    {
        $previousException = $e->getPrevious();

        if ($previousException instanceof TokenInvalidException) {
            $message = 'Token is Invalid';
        } elseif ($previousException instanceof TokenExpiredException) {
            $message = 'Token is Expired';
        } elseif ($previousException instanceof TokenBlacklistedException) {
            $message = 'Token is Blacklisted';
        } elseif ($previousException instanceof JWTException) {
            $message = $previousException->getMessage();
        } else {
            $message = 'Unauthorized';
        }

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => []
        ], 401);
    }
}
