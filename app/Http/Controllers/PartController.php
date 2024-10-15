<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PartController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Part/Index');
    }
}