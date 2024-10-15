<?php

namespace App\JsonApi\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Filters\Concerns\DeserializesValue;
use LaravelJsonApi\Eloquent\Filters\Concerns\IsSingular;

class PartFilters implements Filter
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
     * @return PartFilters
     */
    public static function make(string $name, string $column = null): self
    {
        return new static($name, $column);
    }

    /**
     * PartFilters constructor.
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
        $partFilters = json_decode(urldecode($value), true);

        Log::debug($partFilters);

        $filterableFields = [
            [
                'name' => 'modelNumber',
                'db_column' => 'model_number',
                'is_numeric' => false,
                'is_global_searchable' => true,
            ],
            [
                'name' => 'partType',
                'db_column' => 'part_type',
                'is_numeric' => false,
                'is_global_searchable' => true,
            ],
            [
                'name' => 'manufacturer',
                'db_column' => 'manufacturer',
                'is_numeric' => false,
                'is_global_searchable' => true,
            ],
            [
                'name' => 'listPrice',
                'db_column' => 'list_price',
                'is_numeric' => true,
                'is_global_searchable' => false
            ]
        ];

        $globalSearchableFields = array_filter(
            $filterableFields,
            fn($field) => $field['is_global_searchable']
        );

        if (
            isset($partFilters['global'])
            && $partFilters['global']['value']
            && trim($partFilters['global']['value']) !== ''
            && $globalSearchableFields
        ) {
            $filterValue = trim($partFilters['global']['value']);

            $query->where(function (Builder $query) use ($filterValue, $globalSearchableFields) {
                foreach ($globalSearchableFields as $field) {
                    $this->applyConstraint(
                        $query,
                        $field['db_column'],
                        $filterValue,
                        'startsWith',
                        'or',
                        false
                    );
                }
            });
        }

        foreach ($filterableFields as $field) {
            $fieldName = $field['name'];

            if (!isset($partFilters[$fieldName])) continue;

            if (!isset($partFilters[$fieldName]['constraints'])) continue;

            if (!isset($partFilters[$fieldName]['operator'])) continue;

            $nonEmptyConstraints = array_filter(
                $partFilters[$fieldName]['constraints'],
                function ($constraint) {
                    return isset($constraint['matchMode'])
                        && isset($constraint['value'])
                        && trim($constraint['value']) !== '';
                }
            );

            if ($nonEmptyConstraints) {
                $query->where(function (Builder $query) use ($nonEmptyConstraints, $partFilters, $field) {
                    foreach ($nonEmptyConstraints as $constraint) {
                        $this->applyConstraint(
                            $query,
                            $field['db_column'],
                            $constraint['value'],
                            $constraint['matchMode'],
                            $partFilters[$field['name']]['operator'],
                            $field['is_numeric']
                        );
                    }
                });
            }
        }
    }

    protected function applyConstraint($query, $field, $value, $matchMode, $operator, $isNumeric): void
    {
        $builderOperator = $operator === 'and' ? 'where' : 'orWhere';

        switch ($matchMode) {
            case 'startsWith':
                $query->$builderOperator($field, 'like', $value . '%');
                break;
            case 'contains':
                $query->$builderOperator($field, 'like', '%' . $value . '%');
                break;
            case 'notContains':
                $query->$builderOperator($field, 'not like', '%' . $value . '%');
                break;
            case 'endsWith':
                $query->$builderOperator($field, 'like', '%' . $value);
                break;
            case 'equals':
                if ($isNumeric) {
                    $query->$builderOperator($field, '=', $value);
                } else {
                    $query->$builderOperator($field, 'like', $value);
                }
                break;
            case 'notEquals':
                if ($isNumeric) {
                    $query->$builderOperator($field, '<>', $value);
                } else {
                    $query->$builderOperator($field, 'not like', $value);
                }
                break;
            case 'lt':
                $query->$builderOperator($field, '<', $value);
                break;
            case 'lte':
                $query->$builderOperator($field, '<=', $value);
                break;
            case 'gt':
                $query->$builderOperator($field, '>', $value);
                break;
            case 'gte':
                $query->$builderOperator($field, '>=', $value);
                break;
            default:
                break;
        }
    }
}
