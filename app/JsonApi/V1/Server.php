<?php

namespace App\JsonApi\V1;

use App\JsonApi\V1\Parts\PartSchema;
use App\JsonApi\V1\TeamParts\TeamPartSchema;
use App\JsonApi\V1\Teams\TeamSchema;
use App\Models\TeamPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{

    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/v1';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        Auth::shouldUse('sanctum');

        TeamPart::creating(static function (TeamPart $teamPart): void {
            $teamPart->team()->associate(Session::get('session_team'));
        });
    }

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            TeamSchema::class,
            PartSchema::class,
            TeamPartSchema::class,
        ];
    }
}
