<?php

namespace App\Http\Middleware;

use Closure;
use http\Env\Response;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Symfony\Component\HttpFoundation\Response as ResponseData;

class JwtMiddleware extends BaseMiddleware
{/**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!is_null($user)) {
                $currentUSer = json_encode($user);
                Log::info('User '.$currentUSer);
                Log::info('Method '.$request->method());
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 'Token is Invalid'], ResponseData::HTTP_INTERNAL_SERVER_ERROR);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 'Token is Expired'], ResponseData::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                return response()->json(['status' => 'Authorization Token not found'], ResponseData::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        return $next($request);
    }
}
