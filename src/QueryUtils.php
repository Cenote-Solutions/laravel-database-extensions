<?php

namespace CenoteSolutions\LaravelDatabase;

use Closure;
use Illuminate\Database\Query\Builder;

class QueryUtils
{
    /**
     * @var \CenoteSolutions\LaravelDatabase\QueryUtils
     */
    protected static $instance;

    /**
     * Macro for grouping current where conditions into one.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Closure $callback (optional)
     * @return void
     */
    public function doGroupWheres(Builder $query, Closure $callback = null)
    {
        $wheres = $query->wheres;
        $bindings = $query->bindings['where'];

        $query->wheres = [];
        $query->bindings['where'] = [];

        $query->where(function ($subQuery) use ($wheres, $bindings, $callback) {
            $subQuery->mergeWheres($wheres, $bindings);

            if ($callback) {
                $callback($subQuery);
            }
        });
    }

    /**
     * Add a where NOT constraint to the query.
     * 
     * @param mixed $query
     * @param mixed $column
     * @return mixed
     */
    public function whereNot($query, $column)
    {
        return $query->where($column, null, null, 'and not');
    }

    /**
     * Macro for "orWhereNot"<div class="
     * 
     * @param mixed $query
     * @param mixed $column
     * @return \Closure
     */
    public function orWhereNot($query, $column)
    {
        return $query->where($column, null, null, 'or not');
    }

    /**
     * Get the instance of the query utility.
     * 
     * @return self
     */
    public static function instance()
    {
        return self::$instance ?? (self::$instance = new self);
    }
}
