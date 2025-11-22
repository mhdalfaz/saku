<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    use \App\Traits\ApiResponse;

    public function handle($request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return $this->error("Token expired", 401);
        } catch (TokenInvalidException $e) {
            return $this->error("Invalid token", 401);
        } catch (JWTException $e) {
            return $this->error("Token not found", 401);
        }

        return $next($request);
    }
}