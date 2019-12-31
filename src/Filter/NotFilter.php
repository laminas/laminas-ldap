<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Ldap\Filter;

/**
 * Laminas\Ldap\Filter\NotFilter provides a negation filter.
 */
class NotFilter extends AbstractFilter
{
    /**
     * The underlying filter.
     *
     * @var AbstractFilter
     */
    private $filter;

    /**
     * Creates a Laminas\Ldap\Filter\NotFilter.
     *
     * @param AbstractFilter $filter
     */
    public function __construct(AbstractFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Negates the filter.
     *
     * @return AbstractFilter
     */
    public function negate()
    {
        return $this->filter;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return '(!' . $this->filter->toString() . ')';
    }
}
