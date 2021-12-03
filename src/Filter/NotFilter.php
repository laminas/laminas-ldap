<?php

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
