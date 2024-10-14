<?php

namespace App\JsonApi\V1\Parts;

use App\Models\Part;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PartSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Part::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('partType')->readOnly(),
            Str::make('manufacturer')->readOnly(),
            Str::make('modelNumber')->readOnly(),
            Boolean::make('isActive')->readOnly(),
            Number::make('listPrice')->sortable()->readOnly(),
            Boolean::make('isAssociated')->sortable()->readOnly(),
            Number::make('teamPartId')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            DateTime::make('createdAt')->sortable()->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }

    public function indexQuery(?Request $request, Builder $query): Builder
    {
        $sessionTeamId = $request->session()->get('session_team')->id;

        return $query->select(DB::raw('
                parts.*, 
                CASE
                    WHEN team_parts.id IS NULL
                        THEN false
                    ELSE
                        true
                END as is_associated,
                team_parts.id as team_part_id
            '))
            ->leftjoin('team_parts', function (JoinClause $join) use ($sessionTeamId) {
                $join->on('parts.id', '=', 'team_parts.part_id')
                    ->where('team_parts.team_id', '=', $sessionTeamId);
            })
            ->where('parts.is_active', true);
    }
}
