<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

/** Public information pages linked from the footer. */
final class PageController extends Controller
{
    public function about(): Response
    {
        return $this->view('pages/about');
    }

    public function help(): Response
    {
        return $this->view('pages/help');
    }

    public function privacy(): Response
    {
        return $this->view('pages/privacy');
    }

    public function terms(): Response
    {
        return $this->view('pages/terms');
    }

    public function security(): Response
    {
        return $this->view('pages/security');
    }
}
