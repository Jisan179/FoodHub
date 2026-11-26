<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Response;

final class HomeController extends Controller
{
    public function index(): Response
    {
        $user = $this->app->user();

        return $this->redirect($user ? App::homeFor($user['role']) : '/login');
    }
}
