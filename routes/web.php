<?php

use App\Http\Controllers\PartController;
use App\Http\Controllers\PartUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetTeamController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamPartController;
use App\Models\TeamPart;
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

    Route::get('/teams', [TeamController::class, 'index'])->name('team.index');
    Route::get('/set_team/{team}', SetTeamController::class)->name('set_team');

    Route::get('/parts', [PartController::class, 'index'])->name('part.index');
    Route::get('/part_uploads', [PartUploadController::class, 'index'])->name('part_upload.index');
    Route::get('/part_uploads/create', [PartUploadController::class, 'create'])->name('part_upload.create');
    Route::post('/part_uploads/store', [PartUploadController::class, 'store'])->name('part_upload.store');

    Route::post('/team_parts/store', [TeamPartController::class, 'store'])->name('team_part.store');
    Route::delete('/team_parts/{team_part}', [TeamPartController::class, 'destroy'])->name('team_part.destroy');
});

require __DIR__.'/auth.php';