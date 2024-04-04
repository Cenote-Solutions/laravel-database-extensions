<?php

namespace CenoteSolutions\LaravelDatabase\Query;

use CenoteSolutions\LaravelDatabase\Concerns\MixinUsingQueryUtils;
use Closure;

class BuilderMixin
{
    use MixinUsingQueryUtils;

    /**
     * Macro for grouping current where conditions into one.
     * 
     * @return \Closure
     */
    public function groupWheres()
    {
        return $this->createUtilMacro('doGroupWheres');
    }

    /**
     * Macro for "whereNot"
     * 
     * @return \Closure
     */
    public function whereNot()
    {
        return $this->createUtilMacro('whereNot');
    }

    /**
     * Macro for "orWhereNot"
     * 
     * @return \Closure
     */
    public function orWhereNot()
    {
        return $this->createUtilMacro('orWhereNot');
    }    
}