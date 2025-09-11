<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('filament.admin.pages.dashboard');
        }
        return view('landing-page');
    }
}
