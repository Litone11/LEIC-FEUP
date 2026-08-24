<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Public marketing home page.
     */
    public function __invoke()
    {
        return view('pages.home');
    }
}
