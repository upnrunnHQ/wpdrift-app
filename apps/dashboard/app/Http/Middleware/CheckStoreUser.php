<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckStoreUser
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
        // Added middle var so that only is company single page then this middle ware will be applied
        if ($request->route()->parameters()) {
            $store_details = $request->route()->parameters()['site'];
            if ($store_details != "") {
                if ($store_details->getoriginal()['user_id'] != "") {
                    if ($store_details->getoriginal()['user_id'] != Auth::user()->id) {
                        return redirect('/sites');
                    }
                }
            }
        }
        return $next($request);
    }
}
