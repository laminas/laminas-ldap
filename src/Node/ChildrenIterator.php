<?php

namespace Laminas\Ldap\Node;

use ArrayAccess;
use Countable;
use Iterator;
use Laminas\Ldap;
use Laminas\Ldap\Node;
use RecursiveIterator;

use function array_key_exists;
use function count;
use function current;
use function key;
use function next;
use function reset;

/**
 * Laminas\Ldap\Node\ChildrenIterator provides an iterator to a collection of children nodes.
 */
class ChildrenIterator implements Iterator, Countable, RecursiveIterator, ArrayAccess
{
    /**
     * An array of Laminas\Ldap\Node objects
     */
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the number of child nodes.
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Return the current child.
     * Implements Iterator
     *
     * @return Node
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Return the child'd RDN.
     * Implements Iterator
     *
     * @return string
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Move forward to next child.
     * Implements Iterator
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * Rewind the Iterator to the first child.
     * Implements Iterator
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * Check if there is a current child
     * after calls to rewind() or next().
     * Implements Iterator
     *
     * @return bool
     */
    public function valid()
    {
        return current($this->data) !== false;
    }

    /**
     * Checks if current node has children.
     * Returns whether the current element has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        if ($this->current() instanceof Ldap\Node) {
            return $this->current()->hasChildren();
        }

        return false;
    }

    /**
     * Returns the children for the current node.
     *
     * @return ChildrenIterator
     */
    public function getChildren()
    {
        if ($this->current() instanceof Ldap\Node) {
            return $this->current()->getChildren();
        }

        return null;
    }

    /**
     * Returns a child with a given RDN.
     * Implements ArrayAccess.
     *
     * @param  string $rdn
     * @return array|null
     */
    public function offsetGet($rdn)
    {
        if ($this->offsetExists($rdn)) {
            return $this->data[$rdn];
        }

        return null;
    }

    /**
     * Checks whether a given rdn exists.
     * Implements ArrayAccess.
     *
     * @param  string $rdn
     * @return bool
     */
    public function offsetExists($rdn)
    {
        return array_key_exists($rdn, $this->data);
    }

    /**
     * Does nothing.
     * Implements ArrayAccess.
     *
     * @param string $name
     * @return void
     */
    public function offsetUnset($name)
    {
    }

    /**
     * Does nothing.
     * Implements ArrayAccess.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($name, $value)
    {
    }

    /**
     * Get all children as an array
     *
     * @return array
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
