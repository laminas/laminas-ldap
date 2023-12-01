<?php

namespace Laminas\Ldap\Filter;

use function count;
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
     * @param string $values
     */
    public function __construct($mask, ...$values)
    {
        for ($i = 0, $count = count($values); $i < $count; $i++) {
            $values[$i] = static::escapeValue($values[$i]);
        }
        $filter = vsprintf($mask, $values);
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
