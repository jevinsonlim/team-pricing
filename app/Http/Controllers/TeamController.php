<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Team/Index', [
            'teams' => Team::with(['admins:id,name', 'members:id,name'])->get()
        ]);
    }
}
