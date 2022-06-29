<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckCompanyUser
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
            $company_details = $request->route()->parameters()['company'];
            if ($company_details != "") {
                if ($company_details->getoriginal()['user_id'] != "") {
                    if ($company_details->getoriginal()['user_id'] != Auth::user()->id) {
                        return redirect('/companies');
                    }
                }
            }
        }

        return $next($request);
    }
}
