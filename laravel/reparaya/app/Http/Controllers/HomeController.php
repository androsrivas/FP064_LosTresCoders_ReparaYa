<?php

namespace App\Http\Controllers;

use App\Models\Speciality;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $specialities = Speciality::orderBy('name')->get();

        return view('home', compact('specialities'));
    }
}