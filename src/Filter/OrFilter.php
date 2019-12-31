<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

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
