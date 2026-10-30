<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class PerfilController extends Controller
{
    public function __construct()
    {
        Auth::requireLogin();
    }
}