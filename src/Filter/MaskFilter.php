<?php

namespace Laminas\Ldap\Filter;

use function array_shift;
use function count;
use function func_get_args;
use function vsprintf;

/**
 * Laminas\Ldap\Filter\MaskFilter provides a simple string filter to be used with a mask.
 */
class MaskFilter extends StringFilter
{
    /**
     * Creates a Laminas\Ldap\Filter\MaskFilter.
     *
     * @param string $mask
     * @param string $value,...
     */
    public function __construct($mask, $value)
    {
        $args = func_get_args();
        array_shift($args);
        for ($i = 0, $count = count($args); $i < $count; $i++) {
            $args[$i] = static::escapeValue($args[$i]);
        }
        $filter = vsprintf($mask, $args);
        parent::__construct($filter);
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return $this->filter;
    }
}
