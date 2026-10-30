<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;

final class IncidenciaController extends Controller
{
    public function __construct()
    {
        Auth::requireRole(Roles::PARTICULAR);
    }
}