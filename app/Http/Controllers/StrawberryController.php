<?php

namespace App\Http\Controllers;

class StrawberryController extends Controller
{
    public function index()
    {
        return view('pages.strawberry.home');
    }
}
