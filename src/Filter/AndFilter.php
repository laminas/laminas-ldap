<?php

namespace Laminas\Ldap\Filter;

/**
 * Laminas\Ldap\Filter\AndFilter provides an 'and' filter.
 */
class AndFilter extends AbstractLogicalFilter
{
    /**
     * Creates an 'and' grouping filter.
     *
     * @param array $subfilters
     */
    public function __construct(array $subfilters)
    {
        parent::__construct($subfilters, self::TYPE_AND);
    }
}
