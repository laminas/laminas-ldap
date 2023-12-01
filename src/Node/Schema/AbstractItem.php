<?php

namespace Laminas\Ldap\Node\Schema;

use ArrayAccess;
use Countable;
use Laminas\Ldap\Exception;
use Laminas\Ldap\Exception\BadMethodCallException;
use ReturnTypeWillChange;

use function array_key_exists;
use function count;

/**
 * This class provides a base implementation for managing schema
 * items like objectClass and attributeType.
 *
 * @template-implements ArrayAccess<array-key, mixed>
 */
abstract class AbstractItem implements ArrayAccess, Countable
{
    /**
     * The underlying data
     *
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setData($data);
    }

    /**
     * Sets the data
     *
     * @param  array $data
     * @return AbstractItem Provides a fluid interface
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gets a specific attribute from this item
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * Checks whether a specific attribute exists.
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * @inheritDoc
     *
     * Always throws {@see BadMethodCallException}
     *
     * This method is needed for a full implementation of ArrayAccess
     * @psalm-return never
     * @throws BadMethodCallException
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * @inheritDoc
     *
     * Gets a specific attribute from this item
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @inheritDoc
     *
     * Always throws {@see BadMethodCallException}
     * Implements ArrayAccess.
     *
     * This method is needed for a full implementation of ArrayAccess
     * @psalm-return never
     * @throws BadMethodCallException
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * @inheritDoc
     *
     * Checks whether a specific attribute exists.
     */
    #[ReturnTypeWillChange]
    public function offsetExists($name)
    {
        return $this->__isset($name);
    }

    /**
     * @inheritDoc
     *
     * Returns the number of attributes.
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return count($this->data);
    }
}
