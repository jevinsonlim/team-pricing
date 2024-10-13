<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamPartRequest;
use App\Models\TeamPart;
use Illuminate\Http\Request;

class TeamPartController extends Controller
{
    public function store(Request $request)
    {
        $isTeamPartExisting = TeamPart::query()
            ->where('team_id', $request->session()->get('session_team')->id)
            ->where('part_id', $request->get('part_id'))
            ->exists();

        if ($isTeamPartExisting) return;
        
        TeamPart::create([
            'team_id' => $request->session()->get('session_team')->id,
            'part_id' => $request->get('part_id')
        ]);
    }

    public function destroy(TeamPart $teamPart, Request $request)
    {
        if (!$request->user()->can('destroy', $teamPart)) {
            abort(403);
        }

        $teamPart->delete();
    }
}