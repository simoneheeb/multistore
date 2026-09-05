<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DatabaseHealth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                return response()->json( [
                    'message'   => 'Error Connection With Database',
                    'status'    => 503
                ], 503);
            }
        }

        return $next($request);
    }
}
