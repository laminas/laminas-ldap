<?php

namespace Laminas\Ldap\Filter;

/**
 * Laminas\Ldap\Filter\OrFilter provides an 'or' filter.
 */
class OrFilter extends AbstractLogicalFilter
{
    /**
     * Creates an 'or' grouping filter.
     *
     * @param array $subfilters
     */
    public function __construct(array $subfilters)
    {
        parent::__construct($subfilters, self::TYPE_OR);
    }
}
