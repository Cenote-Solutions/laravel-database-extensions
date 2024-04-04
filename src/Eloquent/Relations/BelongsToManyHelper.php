<?php

namespace CenoteSolutions\LaravelDatabase\Eloquent\Relations;

use Illuminate\Support\Str;

trait BelongsToManyHelper
{
    /**
     * Add a where null clause to the pivot table column.
     * 
     * @param string $column
     * @param bool $boolean (optional)
     * @param bool $not (optional)
     * @return static
     */
    public function wherePivotNull($column, $boolean = 'and', $not = false)
    {
        return $this->wherePivot(
            $this->replacePivotTablePlaceholder($column),
            $not ? '!=' : '=',
            null,
            $boolean
        );
    }

    /**
     * Add a where not null clause to the pivot table column.
     * 
     * @param string $column
     * @param bool $boolean (optional)
     * @return static
     */
    public function wherePivotNotNull($column, $boolean = 'and')
    {
        return $this->wherePivotNull($column, $boolean, true);
    }

    /**
     * Macro to add a where raw clause to the pivot table column
     * 
     * @param string $sql
     * @param mixed $bindings (optional)
     * @param bool $boolean (optional)
     * @return \Closure
     */
    public function wherePivotRaw($sql, $bindings = [], $boolean = 'and')
    {
        return $this->whereNested(function ($query) use ($sql, $bindings, $boolean) {
            $query->whereRaw(
                $this->replacePivotTablePlaceholder($sql), $bindings, $boolean
            );
        });
    }

    /**
     * Replace the placeholder of the pivot table.
     * 
     * @param string $str
     * @return string
     */
    protected function replacePivotTablePlaceholder($str)
    {
        return str_replace(['{pivotTable}', '{pivot_table}'], $this->table, $str);
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Check if the custom pivot class has a custom scope method
        if (is_subclass_of($this->getPivotClass(), HasPivotScopes::class) && Str::startsWith($method, 'pivot')) {
            $scopeMethod = Str::replaceFirst('pivot', 'pivotScope', $method);

            if (method_exists($pivot = $this->newPivot(), $scopeMethod)) {
                $pivot->{$scopeMethod}($this, ...$parameters);

                return $this;
            }
        }

        return parent::__call($method, $parameters);
    }
}