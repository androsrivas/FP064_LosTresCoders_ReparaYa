<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Roles;

final class AdminController extends Controller
{
    public function __construct() 
    {
        Auth::requireRole(Roles::ADMIN);
    }
}