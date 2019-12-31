<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Ldap\Node\Schema\AttributeType;

/**
 * This class provides a contract for schema attribute-types.
 *
 * @category   Laminas
 * @package    Laminas_Ldap
 * @subpackage Schema
 */
interface AttributeTypeInterface
{
    /**
     * Gets the attribute name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the attribute OID
     *
     * @return string
     */
    public function getOid();

    /**
     * Gets the attribute syntax
     *
     * @return string
     */
    public function getSyntax();

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength();

    /**
     * Returns if the attribute is single-valued.
     *
     * @return boolean
     */
    public function isSingleValued();

    /**
     * Gets the attribute description
     *
     * @return string
     */
    public function getDescription();
}
