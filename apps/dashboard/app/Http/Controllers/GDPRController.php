<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GDPRController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show_gdpr_form()
    {
        // Download GDPR information
        return view('show_gdpr');
    }
}
