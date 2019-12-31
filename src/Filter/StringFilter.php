<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Ldap\Filter;

/**
 * Laminas\Ldap\Filter\StringFilter provides a simple custom string filter.
 */
class StringFilter extends AbstractFilter
{
    /**
     * The filter.
     *
     * @var string
     */
    protected $filter;

    /**
     * Creates a Laminas\Ldap\Filter\StringFilter.
     *
     * @param string $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return '(' . $this->filter . ')';
    }
}
