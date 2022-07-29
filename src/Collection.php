<?php

namespace Laminas\Ldap;

use Countable;
use Iterator;
use ReturnTypeWillChange;

use function array_key_exists;

/**
 * Laminas\Ldap\Collection wraps a list of LDAP entries.
 */
class Collection implements Iterator, Countable
{
    /**
     * Iterator
     *
     * @var Collection\DefaultIterator
     */
    protected $iterator;

    /**
     * Current item number
     *
     * @var int
     */
    protected $current = -1;

    /**
     * Container for item caching to speed up multiple iterations
     *
     * @var array
     */
    protected $cache = [];

    public function __construct(Collection\DefaultIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set
     *
     * @return bool
     */
    public function close()
    {
        return $this->iterator->close();
    }

    /**
     * Get all entries as an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this as $item) {
            $data[] = $item;
        }
        return $data;
    }

    /**
     * Get first entry
     *
     * @return array|null
     */
    public function getFirst()
    {
        if ($this->count() < 1) {
            return null;
        }

        $this->rewind();
        return $this->current();
    }

    /**
     * Returns the underlying iterator
     *
     * @return Collection\DefaultIterator
     */
    public function getInnerIterator()
    {
        return $this->iterator;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return $this->iterator->count();
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return array|null
     * @throws Exception\LdapException
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        if ($this->count() < 1) {
            return null;
        }

        if ($this->current < 0) {
            $this->rewind();
        }

        if (! array_key_exists($this->current, $this->cache)) {
            $current = $this->iterator->current();
            if ($current === null) {
                return null;
            }
            $this->cache[$this->current] = $this->createEntry($current);
        }

        return $this->cache[$this->current];
    }

    /**
     * Creates the data structure for the given entry data
     *
     * @param  array $data
     * @return array
     */
    protected function createEntry(array $data)
    {
        return $data;
    }

    /**
     * Return the current result item DN
     *
     * @return string|null
     */
    public function dn()
    {
        if ($this->count() > 0) {
            if ($this->current < 0) {
                $this->rewind();
            }
            return $this->iterator->key();
        }
        return null;
    }

    /**
     * Return the current result item key
     * Implements Iterator
     *
     * @return int|null
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        if ($this->count() > 0) {
            if ($this->current < 0) {
                $this->rewind();
            }
            return $this->current;
        }
        return null;
    }

    /**
     * Move forward to next result item
     * Implements Iterator
     *
     * @throws Exception\LdapException
     */
    #[ReturnTypeWillChange]
    public function next()
    {
        $this->iterator->next();
        $this->current++;
    }

    /**
     * Rewind the Iterator to the first result item
     * Implements Iterator
     *
     * @throws Exception\LdapException
     */
    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->iterator->rewind();
        $this->current = 0;
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function valid()
    {
        if (isset($this->cache[$this->current])) {
            return true;
        }
        return $this->iterator->valid();
    }
}
