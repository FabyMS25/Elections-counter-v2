<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        return view('pages-profile', compact('user'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('pages-profile-settings', compact('user'));
    }
}
