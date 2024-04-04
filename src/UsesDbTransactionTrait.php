<?php

namespace CenoteSolutions\LaravelDatabase;

use Closure;
use Illuminate\Support\Facades\DB;

trait UsesDbTransactionTrait
{
    /**
     * @var \Closure
     */
    protected $dbConnectionResolver;

    /**
     * Call the given callback inside a database transaction and return its result.
     * 
     * @param \Closure $callback
     * @param bool $rethrow (optional)
     * @param mixed $failed (optional)
     * @return mixed
     */
    protected function trans($callback, $rethrow = true, $failed = null)
    {
        $db = $this->resolveDatabaseConnection();
        $rollBack = $this->createTransactionRollBackCallback($db);

        try {
            $db->beginTransaction();

            $result = call_user_func($callback, $commit = $this->createTransactionCommitCallback($db), $rollBack);
            $commit();

            return $result;
        } catch (Exception $e) {
            $rollBack();

            if ($rethrow) {
                throw $e;
            }

            report($e);

            if ($failed instanceof Closure) {
                return $failed($e);
            } else {
                return $failed;
            }
        }
    }

    /**
     * Create a closure to fire a database transaction commit if not yet fired.
     * 
     * @param \Illuminate\Database\Connection $db
     * @return \Closure
     */
    protected function createTransactionCommitCallback($db)
    {
        $committed = false;

        return function () use (&$committed, $db) {
            if (! $committed) {
                $db->commit();
                $committed = true;
            }
        };
    }

    /**
     * Create a closure to rollback the current transaction.
     * 
     * @param \Illuminate\Database\Connection $db
     * @return \Closure
     */
    protected function createTransactionRollBackCallback($db)
    {
        return function () use ($db) {
            $db->rollBack();
        };
    }

    /**
     * Set the resolver callback to resolve the database connection.
     * 
     * @param \Closure $resolver
     * @return void
     */
    protected function setDatabaseConnectionResolver(Closure $resolver)
    {
        $this->dbConnectionResolver = $resolver;
    }

    /**
     * Resolve the database connection.
     * 
     * @return \Illuminate\Database\Connection
     */
    protected function resolveDatabaseConnection()
    {
        return isset($this->dbConnectionResolver) ? call_user_func($this->dbConnectionResolver) : DB::connection();
    }
}