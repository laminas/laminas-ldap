<?php

namespace Laminas\Ldap\Filter;

/**
 * Laminas\Ldap\Filter\NotFilter provides a negation filter.
 */
class NotFilter extends AbstractFilter
{
    /**
     * The underlying filter.
     */
    private AbstractFilter $filter;

    /**
     * Creates a Laminas\Ldap\Filter\NotFilter.
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
