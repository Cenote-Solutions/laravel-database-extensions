<?php

namespace CenoteSolutions\LaravelDatabase\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as LaravelBelongsToMany;
use Illuminate\Support\Str;

class BelongsToMany extends LaravelBelongsToMany
{
    use BelongsToManyHelper;
}