<?php

namespace Laminas\Ldap\Filter;

use function is_string;

/**
 * Laminas\Ldap\Filter\AbstractLogicalFilter provides a base implementation for a grouping filter.
 */
abstract class AbstractLogicalFilter extends AbstractFilter
{
    public const TYPE_AND = '&';
    public const TYPE_OR  = '|';

    /**
     * All the sub-filters for this grouping filter.
     */
    private array $subfilters;

    /**
     * The grouping symbol.
     */
    private string $symbol;

    /**
     * Creates a new grouping filter.
     *
     * @param array  $subfilters
     * @param string $symbol
     * @throws Exception\FilterException
     */
    protected function __construct(array $subfilters, $symbol)
    {
        foreach ($subfilters as $key => $s) {
            if (is_string($s)) {
                $subfilters[$key] = new StringFilter($s);
            } elseif (! $s instanceof AbstractFilter) {
                throw new Exception\FilterException('Only strings or Laminas\Ldap\Filter\AbstractFilter allowed.');
            }
        }
        $this->subfilters = $subfilters;
        $this->symbol     = $symbol;
    }

    /**
     * Adds a filter to this grouping filter.
     *
     * @return AbstractLogicalFilter
     */
    public function addFilter(AbstractFilter $filter)
    {
        $new               = clone $this;
        $new->subfilters[] = $filter;
        return $new;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        $return = '(' . $this->symbol;
        foreach ($this->subfilters as $sub) {
            $return .= $sub->toString();
        }
        $return .= ')';
        return $return;
    }
}
