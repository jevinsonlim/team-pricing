<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyBatchTeamPartRequest;
use App\Http\Requests\StoreBatchTeamPartRequest;
use App\Http\Requests\StoreTeamPartRequest;
use App\Models\TeamPart;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamPartController extends Controller
{
    public function index(Request $request) : Response 
    {
        $sessionTeamId = $request->session()->get('session_team')->id;

        return Inertia::render(
            'TeamPart/Index',
            [
                'teamParts' => TeamPart::query()
                    ->with(['team', 'part'])
                    ->whereRelation('part', 'is_active', true)
                    ->where('team_id', $sessionTeamId)
                    ->get()
                    ->toArray()
            ]
        );
    }

    public function store(StoreTeamPartRequest $request)
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

    public function storeBatch(StoreBatchTeamPartRequest $request)
    {
        $partIds = $request->get('part_ids');

        foreach ($partIds as $partId) {
            TeamPart::upsert(
                [
                    'team_id' => $request->session()->get('session_team')->id,
                    'part_id' => $partId,
                ],
                uniqueBy: ['team_id', 'part_id'],
                update: []
            );
        }
    }

    public function destroy(TeamPart $teamPart, Request $request)
    {
        if (
            !$request->user()->can('delete', $teamPart)
            || $teamPart->team_id != $request->session()->get('session_team')->id
        ) {
            abort(403);
        }

        $teamPart->delete();
    }

    public function destroyBatch(DestroyBatchTeamPartRequest $request)
    {
        $teamPartIds = $request->get('team_part_ids');

        TeamPart::whereIn('id', $teamPartIds)->delete();
    }
}
