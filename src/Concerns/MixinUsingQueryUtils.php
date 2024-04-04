<?php

namespace CenoteSolutions\LaravelDatabase\Concerns;

use CenoteSolutions\LaravelDatabase\QueryUtils;

trait MixinUsingQueryUtils
{
    /**
     * Create a macro that will receive the utility object.
     * 
     * @param string|\Closure $macro
     * @return \Closure
     */
    private function createUtilMacro($macro)
    {
        $utils = QueryUtils::instance();

        if (is_string($macro)) {
            return function () use ($utils, $macro) {
                return $utils->{$macro}($this, ...func_get_args());
            };
        }

        return function () use ($macro, $utils) {
            return $macro->call($this, $utils, ...func_get_args());
        };
    }
}
