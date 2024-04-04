<?php

namespace CenoteSolutions\LaravelDatabase\Eloquent;

use Closure;
use LogicException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JoinFromRelationship
{
    /**
     * @var string
     */
    protected $joinType;

    /**
     * @param string $joinType
     */
    public function __construct($joinType)
    {
        $this->joinType = $joinType;
    }

    /**
     * Apply join on the given relation and query builder.
     * 
     * @param string $relationship
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $columns
     * @param \Closure $handleJoin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function __invoke($relation, $query = null, array $columns, Closure $handleJoin = null)
    {
        $query = $query ?? static::query();
        $relationship = $query->getModel()->{$relation}();

        if ($relationship instanceof BelongsTo) {
            $this->applyRelationJoinQueryBelongsTo($relationship, $query, $columns, $handleJoin);
        } else if ($relationship instanceof HasOne) {
            $this->applyRelationJoinQueryHasOne($relationship, $query, $columns, $handleJoin);
        } else {
           throw new LogicException(sprintf('Cannot apply join on relation %s', $relation));
        }

        return $query;
    }

    /**
     * Add a join clause to the query for a belongsTo relationship.
     * 
     * @param \Illuminate\Database\Eloquent\Relations\BelongsTo $relationship
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $columns
     * @param \Closure $handleJoin
     * @return void
     */
    protected function applyRelationJoinQueryBelongsTo(BelongsTo $relationship, $query, array $columns, Closure $handleJoin = null)
    {
        $this->applyJoin($query, $relationship->getRelated(), function ($join) use ($relationship, $query, $handleJoin) {
            $join->on($relationship->getQualifiedOwnerKeyName(), '=', $relationship->getQualifiedForeignKey());

            if ($handleJoin) {
                $handleJoin($join, $relationship->getRelated(), $relationship->getParent(), $query);
            }
        });

        $this->addSelect($relationship, $query, $columns);
    }

    /**
     * Add a join clause to the query for a hasOne relationship.
     * 
     * @param \Illuminate\Database\Eloquent\Relations\HasOne $relationship
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $columns
     * @param \Closure $handleJoin
     * @return void
     */
    protected function applyRelationJoinQueryHasOne(HasOne $relationship, $query, array $columns, Closure $handleJoin = null)
    {
        $this->applyJoin($query, $relationship->getRelated(), function ($join) use ($relationship, $query, $handleJoin) {
            $join->on($relationship->getQualifiedParentKeyName(), '=', $relationship->getQualifiedForeignKeyName());

            if ($handleJoin) {
                $handleJoin($join, $relationship->getRelated(), $relationship->getParent(), $query);
            }
        });

        $this->addSelect($relationship, $query, $columns);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Closure $callback
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyJoin($query, $model, $callback)
    {
        return $query->{$this->joinType}($model->getTable(), $callback);
    }

    /**
     * Add columns to select to the query
     * 
     * @param \\Illuminate\Database\Eloquent\Relations\Relation $relationship
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $columns
     * @return void
     */
    protected function addSelect($relationship, $query, array $columns)
    {
        foreach ($columns as $column) {
            if ($column instanceof Closure) {
                $query->addSelect($column($relationship->getRelated(), $relationship->getParent()));
            } else {
                $query->addSelect(str_replace('%s', $relationship->getRelated()->getTable(), $column));
            }
        }
    }
}