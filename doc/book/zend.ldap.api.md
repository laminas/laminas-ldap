# API overview

## Configuration / options

The `Laminas\Ldap\Ldap` component accepts an array of options either supplied to the constructor or
through the `setOptions()` method. The permitted options are as follows:

## API Reference

> ### Note
Method names in **italics** are static methods.

### Laminas\\Ldap\\Ldap

`Laminas\Ldap\Ldap` is the base interface into a *LDAP* server. It provides connection and binding
methods as well as methods to operate on the *LDAP* tree.

### Laminas\\Ldap\\Collection

`Laminas\Ldap\Collection` implements *Iterator* to allow for item traversal using `foreach()` and
*Countable* to be able to respond to `count()`. With its protected `createEntry()` method it
provides a simple extension point for developers needing custom result objects.

### Laminas\\Ldap\\Attribute

`Laminas\Ldap\Attribute` is a helper class providing only static methods to manipulate arrays suitable
to the structure used in `Laminas\Ldap\Ldap` data modification methods and to the data format required
by the *LDAP* server. *PHP* data types are converted using `Laminas\Ldap\Converter\Converter` methods.

### Laminas\\Ldap\\Converter\\Converter

`Laminas\Ldap\Converter\Converter` is a helper class providing only static methods to manipulate arrays
suitable to the data format required by the *LDAP* server. *PHP* data types are converted the
following way:

**string**  
No conversion will be done.

**integer and float**  
The value will be converted to a string.

**boolean**  
`TRUE` will be converted to **'TRUE'** and `FALSE` to **'FALSE'**

**object and array**  
The value will be converted to a string by using `serialize()`.

**Date/Time**  
The value will be converted to a string with the following `date()` format *YmdHisO*, UTC timezone
(+0000) will be replaced with a *Z*. For example *01-30-2011 01:17:32 PM GMT-6* will be
*20113001131732-0600* and *30-01-2012 15:17:32 UTC* will be *20120130151732Z*

**resource**  
If a *stream* resource is given, the data will be fetched by calling `stream_get_contents()`.

**others**  
All other data types (namely non-stream resources) will be omitted.

On reading values the following conversion will take place:

**'TRUE'**  
Converted to `TRUE`.

**'FALSE'**  
Converted to `FALSE`.

**others**  
All other strings won't be automatically converted and are passed as they are.

### Laminas\\Ldap\\Dn

`Laminas\Ldap\Dn` provides an object-oriented interface to manipulating *LDAP* distinguished names
(DN). The parameter `$caseFold` that is used in several methods determines the way DN attributes are
handled regarding their case. Allowed values for this parameter are:

**LaminasLdapDn::ATTR\_CASEFOLD\_NONE**  
No case-folding will be done.

**LaminasLdapDn::ATTR\_CASEFOLD\_UPPER**  
All attributes will be converted to upper-case.

**LaminasLdapDn::ATTR\_CASEFOLD\_LOWER**  
All attributes will be converted to lower-case.

The default case-folding is `Laminas\Ldap\Dn::ATTR_CASEFOLD_NONE` and can be set with
`Laminas\Ldap\Dn::setDefaultCaseFold()`. Each instance of `Laminas\Ldap\Dn` can have its own
case-folding-setting. If the `$caseFold` parameter is omitted in method-calls it defaults to the
instance's case-folding setting.

The class implements *ArrayAccess* to allow indexer-access to the different parts of the DN. The
*ArrayAccess*-methods proxy to `Laminas\Ldap\Dn::get($offset, 1, null)` for *offsetGet(integer
$offset)*, to `Laminas\Ldap\Dn::set($offset, $value)` for `offsetSet()` and to
`Laminas\Ldap\Dn::remove($offset, 1)` for `offsetUnset()`. `offsetExists()` simply checks if the index
is within the bounds.

### Laminas\\Ldap\\Filter

### Laminas\\Ldap\\Node

`Laminas\Ldap\Node` includes the magic property accessors `__set()`, `__get()`, `__unset()` and
`__isset()` to access the attributes by their name. They proxy to `Laminas\Ldap\Node::setAttribute()`,
`Laminas\Ldap\Node::getAttribute()`, `Laminas\Ldap\Node::deleteAttribute()` and
`Laminas\Ldap\Node::existsAttribute()` respectively. Furthermore the class implements *ArrayAccess* for
array-style-access to the attributes. `Laminas\Ldap\Node` also implements *Iterator* and
*RecursiveIterator* to allow for recursive tree-traversal.

### Laminas\\Ldap\\Node\\RootDse

The following methods are available on all vendor-specific subclasses.

`Laminas\Ldap\Node\RootDse` includes the magic property accessors `__get()` and `__isset()` to access
the attributes by their name. They proxy to `Laminas\Ldap\Node\RootDse::getAttribute()` and
`Laminas\Ldap\Node\RootDse::existsAttribute()` respectively. `__set()` and `__unset()` are also
implemented but they throw a *BadMethodCallException* as modifications are not allowed on RootDSE
nodes. Furthermore the class implements *ArrayAccess* for array-style-access to the attributes.
`offsetSet()` and `offsetUnset()` also throw a *BadMethodCallException* due ro obvious reasons.

#### OpenLDAP

Additionally the common methods above apply to instances of `Laminas\Ldap\Node\RootDse\OpenLdap`.

> ### Note
Refer to [LDAP Operational Attributes and
Objects](http://www.zytrax.com/books/ldap/ch3/#operational) for information on the attributes of
OpenLDAP RootDSE.

#### ActiveDirectory

Additionally the common methods above apply to instances of
`Laminas\Ldap\Node\RootDse\ActiveDirectory`.

> ### Note
Refer to [RootDSE](http://msdn.microsoft.com/en-us/library/ms684291(VS.85).aspx) for information on
the attributes of Microsoft ActiveDirectory RootDSE.

#### eDirectory

Additionally the common methods above apply to instances of *LaminasLdapNodeRootDseeDirectory*.

> ### Note
Refer to [Getting Information about the LDAP
Server](http://www.novell.com/documentation/edir88/edir88/index.html?page=/documentation/edir88/edir88/data/ah59jqq.html)
for information on the attributes of Novell eDirectory RootDSE.

### Laminas\\Ldap\\Node\\Schema

The following methods are available on all vendor-specific subclasses.

*LaminasLdapNodeSchema* includes the magic property accessors *\_\_get()* and *\_\_isset()* to access
the attributes by their name. They proxy to *LaminasLdapNodeSchema::getAttribute()* and
*LaminasLdapNodeSchema::existsAttribute()* respectively. *\_\_set()* and *\_\_unset()* are also
implemented, but they throw a *BadMethodCallException* as modifications are not allowed on RootDSE
nodes. Furthermore the class implements *ArrayAccess* for array-style-access to the attributes.
*offsetSet()* and *offsetUnset()* also throw a *BadMethodCallException* due to obvious reasons.

Classes representing attribute types and object classes extend *LaminasLdapNodeSchemaAbstractItem*
which provides some core methods to access arbitrary attributes on the underlying *LDAP* node.
*LaminasLdapNodeSchemaAbstractItem* includes the magic property accessors *\_\_get()* and *\_\_isset()*
to access the attributes by their name. Furthermore the class implements *ArrayAccess* for
array-style-access to the attributes. *offsetSet()* and *offsetUnset()* throw a
*BadMethodCallException* as modifications are not allowed on schema information nodes.

#### OpenLDAP

Additionally the common methods above apply to instances of *LaminasLdapNodeSchemaOpenLDAP*.

#### ActiveDirectory

> ### Note
#### Schema browsing on ActiveDirectory servers
Due to restrictions on Microsoft ActiveDirectory servers regarding the number of entries returned by
generic search routines and due to the structure of the ActiveDirectory schema repository, schema
browsing is currently **not** available for Microsoft ActiveDirectory servers.

*LaminasLdapNodeSchemaActiveDirectory* does not provide any additional methods.

### Laminas\\Ldap\\Ldif\\Encoder
