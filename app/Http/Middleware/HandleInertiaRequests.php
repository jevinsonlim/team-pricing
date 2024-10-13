<?php

namespace App\Http\Middleware;

use App\Models\Part;
use App\Models\PartUpload;
use App\Models\Team;
use App\Models\TeamPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        if (!$user) {
            return [
                ...parent::share($request),
                'flash' => [
                    'message' => fn() => $request->session()->get('message')
                ],
            ];
        }

        $user->load(['teamAdminships', 'teamMemberships', 'systemAdminship']);
        $user->setAppends(['teams']);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'session_team' => $request->session()->get('session_team'),
                'can' => [
                    'view_any_team' => $user->can('viewAny', Team::class),
                    'view_any_part' => $user->can('viewAny', Part::class),
                    
                    'view_any_part_upload' => $user->can('viewAny', PartUpload::class),
                    'create_part_upload' => $user->can('create', PartUpload::class),

                    'view_any_team_part' => $user->can('viewAny', TeamPart::class),
                    'create_team_part' => $user->can('create', TeamPart::class),
                    'edit_team_part' => $user->can('edit', TeamPart::class),
                    'upload_team_part' => $user->can('create', TeamPart::class),
                    'download_team_part' => $user->can('viewAny', TeamPart::class),
                ]
            ],
            'flash' => [
                'message' => fn() => $request->session()->get('message')
            ],
        ];
    }
}