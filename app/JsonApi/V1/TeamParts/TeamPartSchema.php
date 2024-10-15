<?php

namespace App\JsonApi\V1\TeamParts;

use App\JsonApi\Filters\TeamPartFilter;
use App\Models\TeamPart;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LaravelJsonApi\Eloquent\Fields\Str;

class TeamPartSchema extends Schema
{

    protected int $maxDepth = 3;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = TeamPart::class;
    
    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            BelongsTo::make('team')->type('teams'),
            BelongsTo::make('part')->type('parts'),
            Str::make('partType')->readOnly(),
            Str::make('manufacturer')->readOnly(),
            Str::make('modelNumber')->readOnly(),
            Number::make('listPrice')->sortable()->readOnly(),
            Number::make('multiplier')->sortable()->readOnly(),
            Number::make('staticPrice')->sortable()->readOnly(),
            Number::make('teamPrice')->sortable()->readOnly(),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
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
            TeamPartFilter::make('team-part-filters')
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

        return $query
            ->select(DB::raw('
                team_parts.*, 
                parts.model_number,
                parts.manufacturer,
                parts.part_type,
                parts.list_price
            '))
            ->join('parts', 'parts.id', '=', 'team_parts.part_id')
            ->where('parts.is_active', true)
            ->where('team_parts.team_id', $sessionTeamId);
    }
}
