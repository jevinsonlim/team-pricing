<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SetTeamController extends Controller
{
    public function __invoke(Team $team, Request $request)
    {  
        if (!$team->members()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }

        $request->session()->put('session_team', $team);

        return Redirect::route('dashboard');
    }
}