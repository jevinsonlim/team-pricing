<?php

namespace App\JsonApi\Filters;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Filters\Concerns\DeserializesValue;
use LaravelJsonApi\Eloquent\Filters\Concerns\IsSingular;

class PartIsAssociated implements Filter
{
    use DeserializesValue;
    use IsSingular;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $column;

    /**
     * Create a new filter.
     *
     * @param string $name
     * @param string|null $column
     * @return PartIsAssociated
     */
    public static function make(string $name, string $column = null): self
    {
        return new static($name, $column);
    }

    /**
     * PartIsAssociated constructor.
     *
     * @param string $name
     * @param string|null $column
     */
    public function __construct(string $name, string $column = null)
    {
        $this->name = $name;
        $this->column = $column ?: Str::underscore($name);
    }

    /**
     * Get the key for the filter.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->name;
    }

    /**
     * Apply the filter to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, $value)
    {
        if ($value === 'true') {
            $query->whereNotNull('team_part_id');
        } else {
            $query->whereNull('team_part_id');
        }

        return $query;
    }
}
