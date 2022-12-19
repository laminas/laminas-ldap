<?php

namespace Laminas\Ldap\Filter;

use Laminas\Ldap\Converter\Converter;

use function array_merge;
use function count;
use function func_get_args;
use function is_array;
use function str_replace;

/**
 * Laminas\Ldap\Filter\AbstractFilter provides a base implementation for filters.
 */
abstract class AbstractFilter
{
    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    abstract public function toString();

    /**
     * Returns a string representation of the filter.
     *
     * @see toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Negates the filter.
     *
     * @return AbstractFilter
     */
    public function negate()
    {
        return new NotFilter($this);
    }

    /**
     * Creates an 'and' filter.
     *
     * @param  AbstractFilter $filter,...
     * @return AndFilter
     */
    public function addAnd($filter)
    {
        $fa   = func_get_args();
        $args = array_merge([$this], $fa);
        return new AndFilter($args);
    }

    /**
     * Creates an 'or' filter.
     *
     * @param  AbstractFilter $filter,...
     * @return OrFilter
     */
    public function addOr($filter)
    {
        $fa   = func_get_args();
        $args = array_merge([$this], $fa);
        return new OrFilter($args);
    }

    /**
     * Escapes the given VALUES according to RFC 2254 so that they can be safely used in LDAP filters.
     *
     * Any control characters with an ACII code < 32 as well as the characters with special meaning in
     * LDAP filters "*", "(", ")", and "\" (the backslash) are converted into the representation of a
     * backslash followed by two hex digits representing the hexadecimal value of the character.
     *
     * @link   http://pear.php.net/package/Net_LDAP2
     * @see    Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     *
     * @template TInput of string|array<string>
     *
     * @param TInput $values Array of DN Values
     *
     * @return ($values is string ? string : ($values is array{string} ? string : string|array<string>))
     */
    public static function escapeValue($values = [])
    {
        if (! is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $key => $val) {
            // Escaping of filter meta characters
            $val = str_replace(['\\', '*', '(', ')'], ['\5c', '\2a', '\28', '\29'], $val);
            // ASCII < 32 escaping
            $val = Converter::ascToHex32($val);
            if (null === $val) {
                $val = '\0'; // apply escaped "null" if string is empty
            }
            $values[$key] = $val;
        }
        return count($values) === 1 ? $values[0] : $values;
    }

    /**
     * Undoes the conversion done by {@link escapeValue()}.
     *
     * Converts any sequences of a backslash followed by two hex digits into the corresponding character.
     *
     * @link   http://pear.php.net/package/Net_LDAP2
     * @see    Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     *
     * @template TInput of string|array<string>
     *
     * @param TInput $values Array of DN Values
     *
     * @return ($values is string ? string : ($values is array{string} ? string : string|array<string>))
     */
    public static function unescapeValue($values = [])
    {
        if (! is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $key => $value) {
            // Translate hex code into ascii
            $values[$key] = Converter::hex32ToAsc($value);
        }
        return count($values) === 1 ? $values[0] : $values;
    }
}
