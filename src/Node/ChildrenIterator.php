<?php

namespace Laminas\Ldap\Node;

use ArrayAccess;
use Countable;
use Iterator;
use Laminas\Ldap;
use Laminas\Ldap\Node;
use RecursiveIterator;
use ReturnTypeWillChange;

use function array_key_exists;
use function count;
use function current;
use function key;
use function next;
use function reset;

/**
 * Laminas\Ldap\Node\ChildrenIterator provides an iterator to a collection of children nodes.
 *
 * @template-implements Iterator<string, Node>
 * @template-implements RecursiveIterator<string, Node>
 * @template-implements ArrayAccess<string, Node>
 */
class ChildrenIterator implements Iterator, Countable, RecursiveIterator, ArrayAccess
{
    /**
     * An array of Laminas\Ldap\Node objects
     *
     * @var array<string, Node>
     */
    private array $data;

    /**
     * @param array<string, Node> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function count()
    {
        return count($this->data);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    /**
     * @inheritDoc
     *
     * Return the child'd RDN.
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function next()
    {
        next($this->data);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->data);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function valid()
    {
        return current($this->data) !== false;
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function hasChildren()
    {
        if ($this->current() instanceof Ldap\Node) {
            return $this->current()->hasChildren();
        }

        return false;
    }

    /**
     * @inheritDoc
     * @return ChildrenIterator|null
     */
    #[ReturnTypeWillChange]
    public function getChildren()
    {
        if ($this->current() instanceof Ldap\Node) {
            return $this->current()->getChildren();
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * Returns a child with a given RDN.
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * Checks whether a given rdn exists.
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @inheritDoc
     *
     * Does nothing.
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
    }

    /**
     * @inheritDoc
     *
     * Does nothing.
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Get all children as an array
     *
     * @return array<string, Node>
     */
    public function toArray()
    {
        $data = [];
        foreach ($this as $rdn => $node) {
            $data[$rdn] = $node;
        }
        return $data;
    }
}
