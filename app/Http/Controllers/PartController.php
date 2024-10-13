<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PartController extends Controller
{
    public function index(Request $request): Response
    {
        $parts = [];

        if ($request->user()->systemAdminship()->exists()) {
            // TODO: correct filtering and pagination...
            $parts = Part::all();
        } else if ($request->user()->teamAdminships) {
            $sessionTeamId = $request->session()->get('session_team')->id;

            $parts = Part::query()
                ->select(DB::raw('
                        parts.*, 
                        CASE
                            WHEN team_parts.id IS NULL
                                THEN false
                            ELSE
                                true
                        END as is_associated'
                ))
                ->leftjoin('team_parts', function (JoinClause $join) use ($sessionTeamId) {
                    $join->on('parts.id', '=', 'team_parts.part_id')
                        ->where('team_parts.team_id', '=', $sessionTeamId);
                })
                ->get();
        }

        return Inertia::render(
            'Part/Index',
            ['parts' => $parts->toArray()]
        );
    }
}