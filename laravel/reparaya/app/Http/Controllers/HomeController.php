<?php

declare(strict_types= 1);

namespace App\Http\Controllers;

use App\Models\Speciality;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __invoke(): View
    {
        $specialities = Speciality::orderBy('name')->get();
        $communities = auth()->user()?->managementCompany?->communities()->orderBy('name')->get()
        ?? collect();

        return view('home', compact('specialities', 'communities'));
    }
}
