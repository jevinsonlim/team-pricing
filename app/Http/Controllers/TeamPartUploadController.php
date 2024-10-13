<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamPartUploadRequest;
use App\Jobs\ProcessTeamPartUpload;
use App\Models\TeamPartUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamPartUploadController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('TeamPartUpload/Index', [
            'teamPartUploads' => TeamPartUpload::latest()->get()
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TeamPartUpload/Create');
    }

    public function store(StoreTeamPartUploadRequest $request): RedirectResponse
    {
        $uploadFile = $request->file('upload_file');
        $uploadFilePath = $uploadFile->storePublicly();

        $newPartUpload = TeamPartUpload::create([
            'user_id' => $request->user()->id,
            'team_id' => $request->session()->get('session_team')->id,
            'filename' => $uploadFile->getClientOriginalName(),
            'upload_file' => $uploadFilePath
        ]);

        ProcessTeamPartUpload::dispatch($newPartUpload);

        $request->session()->flash('message', 'Team part upload created.');

        return redirect(route('team_part_upload.index', absolute: false));
    }
}
