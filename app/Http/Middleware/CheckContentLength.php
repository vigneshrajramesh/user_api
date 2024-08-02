<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckContentLength
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

     protected $maxContentLength = 1048576; //Like 1 MB
    public function handle(Request $request, Closure $next)
    {
        $contentLength = $request->server('CONTENT_LENGTH');

        // Check if the content length exceeds the limit
        if ($contentLength > $this->maxContentLength) {
            return response()->json([
                'status' => 'error',
                'message' => 'Content length exceeds the maximum allowed limit.'
            ], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }
        return $next($request);
    }
}
