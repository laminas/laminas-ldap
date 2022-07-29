<?php

namespace Laminas\Ldap\Node;

use Laminas\Ldap;
use Laminas\Ldap\Node;

/**
 * Laminas\Ldap\Node\Collection provides a collection of nodes.
 */
class Collection extends Ldap\Collection
{
    /**
     * Creates the data structure for the given entry data
     *
     * @param  array $data
     * @return Node
     */
    protected function createEntry(array $data)
    {
        $node = Ldap\Node::fromArray($data, true);
        $node->attachLDAP($this->iterator->getLDAP());
        return $node;
    }

    /**
     * Return the child key (DN).
     * Implements Iterator and RecursiveIterator
     *
     * @return string
     */
    public function key()
    {
        return $this->iterator->key();
    }
}
