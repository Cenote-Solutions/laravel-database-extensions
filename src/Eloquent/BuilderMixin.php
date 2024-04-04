<?php

namespace CenoteSolutions\LaravelDatabase\Eloquent;

use Closure;
use CenoteSolutions\LaravelDatabase\Concerns\MixinUsingQueryUtils;
use Illuminate\Database\Eloquent\Builder;

class BuilderMixin
{   
    use MixinUsingQueryUtils;

    /**
     * Apply the macros to the eloquent builder class.
     * 
     * @return void
     */
    public static function apply()
    {
        $instance = new self;

        foreach ([
            'groupWheres',
            'whereHasDeep',
            'whereHasKey'
        ] as $method) {
            Builder::macro($method, $instance->{$method}());
        }

        foreach ([
            'whereNot',
            'orWhereNot'
        ] as $method) {
            Builder::macro($method, $instance->createUtilMacro($method));
        }
    }

    /**
     * Group the current constraints.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function groupWheres()
    {
        return $this->createUtilMacro(function ($utils, Closure $callback = null) {
            $utils->doGroupWheres($this->getQuery(), $callback);

            return $this;
        });
    }

    /**
     * Macro to apply a deep whereHas condition.
     * 
     * @return \Closure
     */
    public function whereHasDeep()
    {
        $mixin = $this;

        return function ($relations, Closure $callback) use ($mixin) {
            $mixin->recurseWhereHasDeep(
                is_string($relations) ? explode('.', $relations) : $relations, 
                $this, $callback
            );

            return $this;
        };
    }

    /**
     * Create the recursive function needed for whereHasDeep method.
     * 
     * @param \Closure
     */
    public function recurseWhereHasDeep($relations, $query, $callback) 
    {
        if (empty($relations)) {
            $callback($query);
            
            return;
        }

        $relation = array_shift($relations);

        $query->whereHas($relation, function ($subQuery) use ($relations, $callback) {
            $this->recurseWhereHasDeep($relations, $subQuery, $callback);
        });
    }
    
    /**
     * Macro to add a key constraint to a whereHas query.
     * 
     * @return \Closure
     */
    public function whereHasKey()
    {
        return function ($relations, $id) {
            $this->whereHasDeep($relations, function ($query) use ($id) {
                $query->whereKey($id);
            });

            return $this;
        };
    }
}