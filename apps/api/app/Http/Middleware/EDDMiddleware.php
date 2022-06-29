<?php

namespace App\Http\Middleware;

use Closure;

class EDDMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $edd_key = $request->input('edd_key');
        if($edd_key == "" || $edd_key != config('app.edd_key')) {
            return response('Unauthorized.', 401);
        }
        return $next($request);
    }
}
