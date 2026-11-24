<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;

abstract class Controller
{
    protected function json(array $payload, $status = 200)
    {
        return Response::json($payload, $status);
    }

    protected function view($template, array $data = [])
    {
        return View::render($template, $data);
    }

    protected function page($area, $template, array $data = [])
    {
        return View::renderPage($area, $template, $data);
    }
}
