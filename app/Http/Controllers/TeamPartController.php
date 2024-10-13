<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyBatchTeamPartRequest;
use App\Http\Requests\StoreBatchTeamPartRequest;
use App\Http\Requests\StoreTeamPartRequest;
use App\Models\TeamPart;
use Illuminate\Http\Request;

class TeamPartController extends Controller
{
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
            !$request->user()->can('destroy', $teamPart)
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
