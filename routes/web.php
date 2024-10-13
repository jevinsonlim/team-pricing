<?php

use App\Http\Controllers\PartController;
use App\Http\Controllers\PartUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetTeamController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamPartController;
use App\Http\Controllers\TeamPartUploadController;
use App\Models\PartUpload;
use App\Models\Team;
use App\Models\TeamPart;
use App\Models\TeamPartUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) return Redirect::route('dashboard');
    
    return Redirect::route('login');
});

Route::get('/dashboard', function (Request $request) {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/teams', [TeamController::class, 'index'])->name('team.index')->can('viewAny', Team::class);
    Route::get('/set_team/{team}', SetTeamController::class)->name('set_team');

    Route::get('/parts', [PartController::class, 'index'])->name('part.index');
    Route::get('/part_uploads', [PartUploadController::class, 'index'])->name('part_upload.index')->can('viewAny', PartUpload::class);
    Route::get('/part_uploads/create', [PartUploadController::class, 'create'])->name('part_upload.create')->can('create', PartUpload::class);
    Route::post('/part_uploads/store', [PartUploadController::class, 'store'])->name('part_upload.store')->can('create', PartUpload::class);

    Route::get('/team_part_uploads', [TeamPartUploadController::class, 'index'])->name('team_part_upload.index')->can('viewAny', TeamPartUpload::class);
    Route::get('/team_part_uploads/create', [TeamPartUploadController::class, 'create'])->name('team_part_upload.create')->can('create', TeamPartUpload::class);
    Route::post('/team_part_uploads/store', [TeamPartUploadController::class, 'store'])->name('team_part_upload.store')->can('create', TeamPartUpload::class);

    Route::get('/team_parts', [TeamPartController::class, 'index'])->name('team_part.index')->can('viewAny', TeamPart::class);
    Route::post('/team_parts/store', [TeamPartController::class, 'store'])->name('team_part.store')->can('create', TeamPart::class);
    Route::post('/team_parts/store_batch', [TeamPartController::class, 'storeBatch'])->name('team_part.store_batch')->can('create', TeamPart::class);
    Route::delete('/team_parts/{team_part}', [TeamPartController::class, 'destroy'])->name('team_part.destroy')->can('destroy', TeamPart::class);
    Route::post('/team_parts/destroy_batch', [TeamPartController::class, 'destroyBatch'])->name('team_part.destroy_batch')->can('destroy', TeamPart::class);
});

require __DIR__.'/auth.php';