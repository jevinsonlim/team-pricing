<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartUploadRequest;
use App\Jobs\ProcessPartUpload;
use App\Models\PartUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PartUploadController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('PartUpload/Index', [
            'partUploads' => PartUpload::latest()->get()
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PartUpload/Create');
    }

    public function store(StorePartUploadRequest $request): RedirectResponse
    {
        $uploadFile = $request->file('upload_file');
        $uploadFilePath = $uploadFile->storePublicly();

        $newPartUpload = PartUpload::create([
            'user_id' => $request->user()->id,
            'filename' => $uploadFile->getClientOriginalName(),
            'upload_file' => $uploadFilePath
        ]);

        ProcessPartUpload::dispatch($newPartUpload);

        $request->session()->flash('message', 'Part upload created.');

        return redirect(route('part_upload.index', absolute: false));
    }
}