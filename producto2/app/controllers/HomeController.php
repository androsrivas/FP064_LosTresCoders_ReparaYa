<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TipoServicio;

class HomeController extends Controller
{
    public function index(): void 
    {
        $this->view('home/home', [
            'titulo'         => 'ReparaYa',
            'especialidades' => TipoServicio::all(),
        ]);
    }
}