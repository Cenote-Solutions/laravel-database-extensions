<?php

namespace CenoteSolutions\LaravelDatabase\Eloquent;

use Illuminate\Database\Eloquent\Model;

trait ModelHelpers
{
    /**
     * Get all of the IDs from the given mixed value.
     *
     * @param \Traversable $value
     * @return array
     */
    public function parseIds($values)
    {
        $result = [];

        foreach ($values as $value) {
            $result[] = $this->parseId($value);
        }

        return $result;
    }

    /**
     * Alias of parseIds
     * 
     * @see self::parseIds
     */
    public function parseKeys($values)
    {
        return $this->parseIds($values);
    }

    /**
     * Get the ID from the given mixed value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function parseId($value)
    {
        return $value instanceof Model ? $value->getKey() : $value;
    }

    /**
     * Alias of parseId
     * 
     * @see self::parseId
     */
    public function parseKey($value)
    {
        return $this->parseId($value);
    }
}