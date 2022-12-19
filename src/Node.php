<?php

namespace Laminas\Ldap;

use ArrayAccess;
use Iterator;
use Laminas\EventManager\EventManager;
use Laminas\Ldap\Node\Collection;
use RecursiveIterator;
use ReturnTypeWillChange;

use function array_key_exists;
use function array_merge;
use function class_exists;
use function count;
use function in_array;
use function is_array;
use function is_string;
use function strtolower;

/**
 * Laminas\Ldap\Node provides an object oriented view into a LDAP node.
 *
 * @template-implements Iterator<string, Node>
 * @template-implements RecursiveIterator<string, Node>
 */
class Node extends Node\AbstractNode implements Iterator, RecursiveIterator
{
    /**
     * Holds the node's new Dn if node is renamed.
     *
     * @var Dn
     */
    protected $newDn;

    /**
     * Holds the node's original attributes (as loaded).
     *
     * @var array
     */
    protected $originalData;

    /**
     * This node will be added
     *
     * @var bool
     */
    protected $new;

    /**
     * This node will be deleted
     *
     * @var bool
     */
    protected $delete;

    /**
     * Holds the connection to the LDAP server if in connected mode.
     *
     * @var Ldap
     */
    protected $ldap;

    /**
     * Holds an array of the current node's children.
     *
     * @var array<string, Node>|null
     */
    protected $children;

    /**
     * Controls iteration status
     */
    private bool $iteratorRewind = false;

    /** @var EventManager */
    protected $events;

    /**
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param  array   $data
     * @param  bool $fromDataSource
     * @throws Exception\LdapException
     */
    protected function __construct(Dn $dn, array $data, $fromDataSource, ?Ldap $ldap = null)
    {
        parent::__construct($dn, $data, $fromDataSource);
        if ($ldap !== null) {
            $this->attachLdap($ldap);
        } else {
            $this->detachLdap();
        }
    }

    /**
     * Serialization callback
     *
     * Only Dn and attributes will be serialized.
     *
     * @return array
     */
    public function __sleep()
    {
        return [
            'dn',
            'currentData',
            'newDn',
            'originalData',
            'new',
            'delete',
            'children',
        ];
    }

    /**
     * Deserialization callback
     *
     * Enforces a detached node.
     */
    public function __wakeup()
    {
        $this->detachLdap();
    }

    /**
     * Gets the current LDAP connection.
     *
     * @return Ldap
     * @throws Exception\LdapException
     */
    public function getLdap()
    {
        if ($this->ldap === null) {
            throw new Exception\LdapException(
                null,
                'No LDAP connection specified.',
                Exception\LdapException::LDAP_OTHER
            );
        }

        return $this->ldap;
    }

    /**
     * Attach node to an LDAP connection
     *
     * This is an offline method.
     *
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function attachLdap(Ldap $ldap)
    {
        if (! Dn::isChildOf($this->_getDn(), $ldap->getBaseDn())) {
            throw new Exception\LdapException(
                null,
                'LDAP connection is not responsible for given node.',
                Exception\LdapException::LDAP_OTHER
            );
        }

        if ($ldap !== $this->ldap) {
            $this->ldap = $ldap;
            if (is_array($this->children)) {
                foreach ($this->children as $child) {
                    $child->attachLdap($ldap);
                }
            }
        }

        return $this;
    }

    /**
     * Detach node from LDAP connection
     *
     * This is an offline method.
     *
     * @return Node Provides a fluid interface
     */
    public function detachLdap()
    {
        $this->ldap = null;
        if (is_array($this->children)) {
            foreach ($this->children as $child) {
                $child->detachLdap();
            }
        }

        return $this;
    }

    /**
     * Checks if the current node is attached to a LDAP server.
     *
     * This is an offline method.
     *
     * @return bool
     */
    public function isAttached()
    {
        return $this->ldap !== null;
    }

    /**
     * Trigger an event
     *
     * @param  string             $event Event name
     * @param array|ArrayAccess $argv Array of arguments; typically, should be associative
     */
    protected function triggerEvent($event, $argv = [])
    {
        $events = $this->getEventManager();
        if (! $events) {
            return;
        }
        $events->trigger($event, $this, $argv);
    }

    /**
     * @param  array   $data
     * @param  bool $fromDataSource
     * @throws Exception\LdapException
     */
    protected function loadData(array $data, $fromDataSource)
    {
        parent::loadData($data, $fromDataSource);
        if ($fromDataSource === true) {
            $this->originalData = $data;
        } else {
            $this->originalData = [];
        }
        $this->children = null;
        $this->markAsNew($fromDataSource !== true);
        $this->markAsToBeDeleted(false);
    }

    /**
     * Factory method to create a new detached Laminas\Ldap\Node for a given DN.
     *
     * @param  string|array|Dn $dn
     * @param  array           $objectClass
     * @return Node
     * @throws Exception\LdapException
     */
    public static function create($dn, array $objectClass = [])
    {
        if (is_string($dn) || is_array($dn)) {
            $dn = Dn::factory($dn);
        } elseif ($dn instanceof Dn) {
            $dn = clone $dn;
        } else {
            throw new Exception\LdapException(null, '$dn is of a wrong data type.');
        }
        $new = new static($dn, [], false, null);
        $new->ensureRdnAttributeValues();
        $new->setAttribute('objectClass', $objectClass);

        return $new;
    }

    /**
     * Factory method to create an attached Laminas\Ldap\Node for a given DN.
     *
     * @param  string|array|Dn $dn
     * @return Node|null
     * @throws Exception\LdapException
     */
    public static function fromLdap($dn, Ldap $ldap)
    {
        if (is_string($dn) || is_array($dn)) {
            $dn = Dn::factory($dn);
        } elseif ($dn instanceof Dn) {
            $dn = clone $dn;
        } else {
            throw new Exception\LdapException(null, '$dn is of a wrong data type.');
        }
        $data = $ldap->getEntry($dn, ['*', '+'], true);
        if ($data === null) {
            return;
        }
        return new static($dn, $data, true, $ldap);
    }

    /**
     * Factory method to create a detached Laminas\Ldap\Node from array data.
     *
     * @param  array   $data
     * @param  bool $fromDataSource
     * @return Node
     * @throws Exception\LdapException
     */
    public static function fromArray(array $data, $fromDataSource = false)
    {
        if (! array_key_exists('dn', $data)) {
            throw new Exception\LdapException(null, '\'dn\' key is missing in array.');
        }
        if (is_string($data['dn']) || is_array($data['dn'])) {
            $dn = Dn::factory($data['dn']);
        } elseif ($data['dn'] instanceof Dn) {
            $dn = clone $data['dn'];
        } else {
            throw new Exception\LdapException(null, '\'dn\' key is of a wrong data type.');
        }
        $fromDataSource = $fromDataSource === true;
        $new            = new static($dn, $data, $fromDataSource, null);
        $new->ensureRdnAttributeValues();

        return $new;
    }

    /**
     * Ensures that teh RDN attributes are correctly set.
     *
     * @param  bool $overwrite True to overwrite the RDN attributes
     * @return void
     */
    protected function ensureRdnAttributeValues($overwrite = false)
    {
        foreach ($this->getRdnArray() as $key => $value) {
            if (! array_key_exists($key, $this->currentData) || $overwrite) {
                Attribute::setAttribute($this->currentData, $key, $value, false);
            } elseif (! in_array($value, $this->currentData[$key])) {
                Attribute::setAttribute($this->currentData, $key, $value, true);
            }
        }
    }

    /**
     * Marks this node as new.
     *
     * Node will be added (instead of updated) on calling update() if $new is true.
     *
     * @param  bool $new
     */
    protected function markAsNew($new)
    {
        $this->new = (bool) $new;
    }

    /**
     * Tells if the node is considered as new (not present on the server)
     *
     * Please note, that this doesn't tell you if the node is present on the server.
     * Use {@link exists()} to see if a node is already there.
     *
     * @return bool
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Marks this node as to be deleted.
     *
     * Node will be deleted on calling update() if $delete is true.
     *
     * @param  bool $delete
     */
    protected function markAsToBeDeleted($delete)
    {
        $this->delete = (bool) $delete;
    }

    /**
     * Is this node going to be deleted once update() is called?
     *
     * @return bool
     */
    public function willBeDeleted()
    {
        return $this->delete;
    }

    /**
     * Marks this node as to be deleted
     *
     * Node will be deleted on calling update() if $delete is true.
     *
     * @return Node Provides a fluid interface
     */
    public function delete()
    {
        $this->markAsToBeDeleted(true);

        return $this;
    }

    /**
     * Is this node going to be moved once update() is called?
     *
     * @return bool
     */
    public function willBeMoved()
    {
        if ($this->isNew() || $this->willBeDeleted()) {
            return false;
        } elseif ($this->newDn !== null) {
            // non-strict comparison necessary here, as we compare two objects by value
            return $this->dn != $this->newDn; // phpcs:ignore
        }

        return false;
    }

    /**
     * Sends all pending changes to the LDAP server
     *
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     * @trigger pre-delete
     * @trigger post-delete
     * @trigger pre-add
     * @trigger post-add
     * @trigger pre-rename
     * @trigger post-rename
     * @trigger pre-update
     * @trigger post-update
     */
    public function update(?Ldap $ldap = null)
    {
        if ($ldap !== null) {
            $this->attachLdap($ldap);
        }
        $ldap = $this->getLdap();
        if (! $ldap instanceof Ldap) {
            throw new Exception\LdapException(null, 'No LDAP connection available');
        }

        if ($this->willBeDeleted()) {
            if ($ldap->exists($this->dn)) {
                $this->triggerEvent('pre-delete');
                $ldap->delete($this->dn);
                $this->triggerEvent('post-delete');
            }
            return $this;
        }

        if ($this->isNew()) {
            $this->triggerEvent('pre-add');
            $data = $this->getData();
            $ldap->add($this->_getDn(), $data);
            $this->loadData($data, true);
            $this->triggerEvent('post-add');

            return $this;
        }

        $changedData = $this->getChangedData();
        if ($this->willBeMoved()) {
            $this->triggerEvent('pre-rename');
            $recursive = $this->hasChildren();
            $ldap->rename($this->dn, $this->newDn, $recursive, false);
            foreach ($this->newDn->getRdn() as $key => $value) {
                if (array_key_exists($key, $changedData)) {
                    unset($changedData[$key]);
                }
            }
            $this->dn    = $this->newDn;
            $this->newDn = null;
            $this->triggerEvent('post-rename');
        }
        if (count($changedData) > 0) {
            $this->triggerEvent('pre-update');
            $ldap->update($this->_getDn(), $changedData);
            $this->triggerEvent('post-update');
        }
        $this->originalData = $this->currentData;

        return $this;
    }

    /**
     * Gets the DN of the current node as a Laminas\Ldap\Dn.
     *
     * This is an offline method.
     *
     * @return Dn
     */
    // @codingStandardsIgnoreStart
    protected function _getDn()
    {
        // @codingStandardsIgnoreEnd
        return $this->newDn ?? parent::_getDn();
    }

    /**
     * Gets the current DN of the current node as a Laminas\Ldap\Dn.
     * The method returns a clone of the node's DN to prohibit modification.
     *
     * This is an offline method.
     *
     * @return Dn
     */
    public function getCurrentDn()
    {
        return clone parent::_getDn();
    }

    /**
     * Sets the new DN for this node
     *
     * This is an offline method.
     *
     * @param  Dn|string|array $newDn
     * @throws Exception\LdapException
     * @return Node Provides a fluid interface
     */
    public function setDn($newDn)
    {
        if ($newDn instanceof Dn) {
            $this->newDn = clone $newDn;
        } else {
            $this->newDn = Dn::factory($newDn);
        }
        $this->ensureRdnAttributeValues(true);

        return $this;
    }

    /**
     * {@see setDn()}
     *
     * This is an offline method.
     *
     * @param  Dn|string|array $newDn
     * @throws Exception\LdapException
     * @return Node Provides a fluid interface
     */
    public function move($newDn)
    {
        return $this->setDn($newDn);
    }

    /**
     * {@see setDn()}
     *
     * This is an offline method.
     *
     * @param  Dn|string|array $newDn
     * @throws Exception\LdapException
     * @return Node Provides a fluid interface
     */
    public function rename($newDn)
    {
        return $this->setDn($newDn);
    }

    /**
     * Sets the objectClass.
     *
     * This is an offline method.
     *
     * @param  array|string $value
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function setObjectClass($value)
    {
        $this->setAttribute('objectClass', $value);

        return $this;
    }

    /**
     * Appends to the objectClass.
     *
     * This is an offline method.
     *
     * @param  array|string $value
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function appendObjectClass($value)
    {
        $this->appendToAttribute('objectClass', $value);

        return $this;
    }

    /**
     * Returns a LDIF representation of the current node
     *
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function toLdif(array $options = [])
    {
        $attributes = array_merge(['dn' => $this->getDnString()], $this->getData(false));

        return Ldif\Encoder::encode($attributes, $options);
    }

    /**
     * Gets changed node data.
     *
     * The array contains all changed attributes.
     * This format can be used in {@link Laminas\Ldap\Ldap::add()} and {@link Laminas\Ldap\Ldap::update()}.
     *
     * This is an offline method.
     *
     * @return array
     */
    public function getChangedData()
    {
        $changed = [];
        foreach ($this->currentData as $key => $value) {
            if (! array_key_exists($key, $this->originalData) && ! empty($value)) {
                $changed[$key] = $value;
            } elseif ($this->originalData[$key] !== $this->currentData[$key]) {
                $changed[$key] = $value;
            }
        }

        return $changed;
    }

    /**
     * Returns all changes made.
     *
     * This is an offline method.
     *
     * @return array
     */
    public function getChanges()
    {
        $changes = [
            'add'     => [],
            'delete'  => [],
            'replace' => [],
        ];
        foreach ($this->currentData as $key => $value) {
            if (! array_key_exists($key, $this->originalData) && ! empty($value)) {
                $changes['add'][$key] = $value;
            } elseif (count($this->originalData[$key]) === 0 && ! empty($value)) {
                $changes['add'][$key] = $value;
            } elseif ($this->originalData[$key] !== $this->currentData[$key]) {
                if (empty($value)) {
                    $changes['delete'][$key] = $value;
                } else {
                    $changes['replace'][$key] = $value;
                }
            }
        }

        return $changes;
    }

    /**
     * Sets a LDAP attribute.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function setAttribute($name, $value)
    {
        $this->_setAttribute($name, $value, false);
        return $this;
    }

    /**
     * Appends to a LDAP attribute.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function appendToAttribute($name, $value)
    {
        $this->_setAttribute($name, $value, true);

        return $this;
    }

    /**
     * Checks if the attribute can be set and sets it accordingly.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  bool $append
     * @throws Exception\LdapException
     */
    // @codingStandardsIgnoreStart
    protected function _setAttribute($name, $value, $append)
    {
        // @codingStandardsIgnoreEnd
        $this->assertChangeableAttribute($name);
        Attribute::setAttribute($this->currentData, $name, $value, $append);
    }

    /**
     * Sets a LDAP date/time attribute.
     *
     * This is an offline method.
     *
     * @param  string        $name
     * @param  int|array $value
     * @param  bool       $utc
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function setDateTimeAttribute($name, $value, $utc = false)
    {
        $this->_setDateTimeAttribute($name, $value, $utc, false);
        return $this;
    }

    /**
     * Appends to a LDAP date/time attribute.
     *
     * This is an offline method.
     *
     * @param  string        $name
     * @param  int|array $value
     * @param  bool       $utc
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function appendToDateTimeAttribute($name, $value, $utc = false)
    {
        $this->_setDateTimeAttribute($name, $value, $utc, true);

        return $this;
    }

    /**
     * Checks if the attribute can be set and sets it accordingly.
     *
     * @param  string        $name
     * @param  int|array $value
     * @param  bool       $utc
     * @param  bool       $append
     * @throws Exception\LdapException
     */
    // @codingStandardsIgnoreStart
    protected function _setDateTimeAttribute($name, $value, $utc, $append)
    {
        // @codingStandardsIgnoreEnd
        $this->assertChangeableAttribute($name);
        Attribute::setDateTimeAttribute($this->currentData, $name, $value, $utc, $append);
    }

    /**
     * Sets a LDAP password.
     *
     * @param  string $password
     * @param  string $hashType
     * @param  string $attribName
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function setPasswordAttribute(
        $password,
        $hashType = Attribute::PASSWORD_HASH_MD5,
        $attribName = 'userPassword'
    ) {
        $this->assertChangeableAttribute($attribName);
        Attribute::setPassword($this->currentData, $password, $hashType, $attribName);

        return $this;
    }

    /**
     * Deletes a LDAP attribute.
     *
     * This method deletes the attribute.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function deleteAttribute($name)
    {
        if ($this->existsAttribute($name, true)) {
            $this->_setAttribute($name, null, false);
        }

        return $this;
    }

    /**
     * Removes duplicate values from a LDAP attribute
     *
     * @param  string $attribName
     * @return void
     */
    public function removeDuplicatesFromAttribute($attribName)
    {
        Attribute::removeDuplicatesFromAttribute($this->currentData, $attribName);
    }

    /**
     * Remove given values from a LDAP attribute
     *
     * @param  string      $attribName
     * @param  mixed|array $value
     * @return void
     */
    public function removeFromAttribute($attribName, $value)
    {
        Attribute::removeFromAttribute($this->currentData, $attribName, $value);
    }

    /**
     * @param  string $name
     * @return bool
     * @throws Exception\LdapException
     */
    protected function assertChangeableAttribute($name)
    {
        $name = strtolower($name);
        $rdn  = $this->getRdnArray(Dn::ATTR_CASEFOLD_LOWER);

        if ($name === 'dn') {
            throw new Exception\LdapException(null, 'DN cannot be changed.');
        }

        if (array_key_exists($name, $rdn)) {
            throw new Exception\LdapException(null, 'Cannot change attribute because it\'s part of the RDN');
        }

        if (in_array($name, static::$systemAttributes)) {
            throw new Exception\LdapException(null, 'Cannot change attribute because it\'s read-only');
        }

        return true;
    }

    /**
     * Sets a LDAP attribute.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @param  mixed  $value
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * Deletes a LDAP attribute.
     *
     * This method deletes the attribute.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @throws Exception\LdapException
     */
    public function __unset($name)
    {
        $this->deleteAttribute($name);
    }

    /**
     * @inheritDoc
     *
     * Sets a LDAP attribute.
     *
     * This is an offline method.
     * @return void
     * @throws Exception\LdapException
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * @inheritDoc
     *
     * Deletes a LDAP attribute.
     *
     * This method deletes the attribute.
     *
     * This is an offline method.
     * @return void
     * @throws Exception\LdapException
     */
    public function offsetUnset($offset)
    {
        $this->deleteAttribute($offset);
    }

    /**
     * Check if node exists on LDAP.
     *
     * This is an online method.
     *
     * @return bool
     * @throws Exception\LdapException
     */
    public function exists(?Ldap $ldap = null)
    {
        if ($ldap !== null) {
            $this->attachLdap($ldap);
        }
        $ldap = $this->getLdap();

        return $ldap->exists($this->_getDn());
    }

    /**
     * Reload node attributes from LDAP.
     *
     * This is an online method.
     *
     * @return Node Provides a fluid interface
     * @throws Exception\LdapException
     */
    public function reload(?Ldap $ldap = null)
    {
        if ($ldap !== null) {
            $this->attachLdap($ldap);
        }
        $ldap = $this->getLdap();
        parent::reload($ldap);

        return $this;
    }

    /**
     * Search current subtree with given options.
     *
     * This is an online method.
     *
     * @param  string|Filter\AbstractFilter $filter
     * @param  int                      $scope
     * @param  string                       $sort
     * @return Collection
     * @throws Exception\LdapException
     */
    public function searchSubtree($filter, $scope = Ldap::SEARCH_SCOPE_SUB, $sort = null)
    {
        return $this->getLdap()->search(
            $filter,
            $this->_getDn(),
            $scope,
            ['*', '+'],
            $sort,
            Collection::class
        );
    }

    /**
     * Count items in current subtree found by given filter.
     *
     * This is an online method.
     *
     * @param  string|Filter\AbstractFilter $filter
     * @param  int                      $scope
     * @return int
     * @throws Exception\LdapException
     */
    public function countSubtree($filter, $scope = Ldap::SEARCH_SCOPE_SUB)
    {
        return $this->getLdap()->count($filter, $this->_getDn(), $scope);
    }

    /**
     * Count children of current node.
     *
     * This is an online method.
     *
     * @return int
     * @throws Exception\LdapException
     */
    public function countChildren()
    {
        return $this->countSubtree('(objectClass=*)', Ldap::SEARCH_SCOPE_ONE);
    }

    /**
     * Gets children of current node.
     *
     * This is an online method.
     *
     * @param  string|Filter\AbstractFilter $filter
     * @param  string                       $sort
     * @return Collection
     * @throws Exception\LdapException
     */
    public function searchChildren($filter, $sort = null)
    {
        return $this->searchSubtree($filter, Ldap::SEARCH_SCOPE_ONE, $sort);
    }

    /**
     * @inheritDoc
     *
     * Checks if current node has children.
     * Returns whether the current element has children.
     *
     * Can be used offline but returns false if children have not been retrieved yet.
     * @throws Exception\LdapException
     */
    #[ReturnTypeWillChange]
    public function hasChildren()
    {
        if (! is_array($this->children)) {
            if ($this->isAttached()) {
                return $this->countChildren() > 0;
            }
            return false;
        }
        return count($this->children) > 0;
    }

    /**
     * Returns the children for the current node.
     *
     * Can be used offline but returns an empty array if children have not been retrieved yet.
     *
     * @return Node\ChildrenIterator
     * @throws Exception\LdapException
     */
    #[ReturnTypeWillChange]
    public function getChildren()
    {
        if (! is_array($this->children)) {
            $this->children = [];
            if ($this->isAttached()) {
                $children = $this->searchChildren('(objectClass=*)', null);
                foreach ($children as $child) {
                    $this->children[$child->getRdnString(Dn::ATTR_CASEFOLD_LOWER)] = $child;
                }
            }
        }

        return new Node\ChildrenIterator($this->children);
    }

    /**
     * Returns the parent of the current node.
     *
     * @return Node
     * @throws Exception\LdapException
     */
    public function getParent(?Ldap $ldap = null)
    {
        if ($ldap !== null) {
            $this->attachLdap($ldap);
        }
        $ldap     = $this->getLdap();
        $parentDn = $this->_getDn()->getParentDn(1);

        return static::fromLdap($parentDn, $ldap);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this;
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->getRdnString();
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function next()
    {
        $this->iteratorRewind = false;
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->iteratorRewind = true;
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function valid()
    {
        return $this->iteratorRewind;
    }

    /**
     * Attempt to marshal an EventManager instance.
     *
     * If an instance is already available, return it.
     *
     * If the laminas-eventmanager component is not present, return nothing.
     *
     * Otherwise, marshal the instance in a version-agnostic way, and return
     * it.
     *
     * @return null|EventManager
     */
    private function getEventManager()
    {
        if ($this->events) {
            return $this->events;
        }

        if (! class_exists(EventManager::class)) {
            return;
        }

        $this->events = new EventManager();
        $this->events->setIdentifiers([self::class]);
        return $this->events;
    }
}
