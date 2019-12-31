# Tools

## Creation and modification of DN strings

## Using the filter API to create search filters

**Create simple LDAP filters**

```php
$f1  = Laminas\Ldap\Filter::equals('name', 'value');         // (name=value)
$f2  = Laminas\Ldap\Filter::begins('name', 'value');         // (name=value*)
$f3  = Laminas\Ldap\Filter::ends('name', 'value');           // (name=*value)
$f4  = Laminas\Ldap\Filter::contains('name', 'value');       // (name=*value*)
$f5  = Laminas\Ldap\Filter::greater('name', 'value');        // (name>value)
$f6  = Laminas\Ldap\Filter::greaterOrEqual('name', 'value'); // (name>=value)
$f7  = Laminas\Ldap\Filter::less('name', 'value');           // (name<value)
$f8  = Laminas\Ldap\Filter::lessOrEqual('name', 'value');    // (name<=value)
$f9  = Laminas\Ldap\Filter::approx('name', 'value');         // (name~=value)
$f10 = Laminas\Ldap\Filter::any('name');                     // (name=*)
```

**Create more complex LDAP filters**

```php
$f1 = Laminas\Ldap\Filter::ends('name', 'value')->negate(); // (!(name=*value))

$f2 = Laminas\Ldap\Filter::equals('name', 'value');
$f3 = Laminas\Ldap\Filter::begins('name', 'value');
$f4 = Laminas\Ldap\Filter::ends('name', 'value');

// (&(name=value)(name=value*)(name=*value))
$f5 = Laminas\Ldap\Filter::andFilter($f2, $f3, $f4);

// (|(name=value)(name=value*)(name=*value))
$f6 = Laminas\Ldap\Filter::orFilter($f2, $f3, $f4);
```

## Modify LDAP entries using the Attribute API
