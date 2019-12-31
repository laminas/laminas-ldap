# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.1 - 2016-05-23

### Added

- [zendframework/zend-ldap#48](https://github.com/zendframework/zend-ldap/pull/48) adds and publishes
  the documentation to https://docs.laminas.dev/laminas-ldap/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-ldap#47](https://github.com/zendframework/zend-ldap/pull/47) Fixes a BC-Break caused
  by the missing default-ErrorHandler

## 2.7.0 - 2016-04-21

### Added

- [zendframework/zend-ldap#43](https://github.com/zendframework/zend-ldap/pull/43) Adds possibility
  to use [Laminas\StdLib](https://github.com/laminas/laminas-stdlib) and
  [Laminas\EventManager](https://github.com/zendframework/zend-eventmanager) in
  Version 3
- Support for PHP7

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-ldap#21](https://github.com/zendframework/zend-ldap/pull/21) Removes dependency
  Laminas\StdLib

### Fixed

- [zendframework/zend-ldap#17](https://github.com/zendframework/zend-ldap/issues/17) Fixes HHVM builds
- [zendframework/zend-ldap#44](https://github.com/zendframework/zend-ldap/pull/40) Fixes broken builds
  in PHP7 due to faulty sorting-test
- [zendframework/zend-ldap#40](https://github.com/zendframework/zend-ldap/pull/40) Fixes connection test
  that failed due to different failure messages in PHP5 and 7

## 2.6.1 - 2016-04-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-ldap#19](https://github.com/zendframework/zend-ldap/pull/20) checks whether the
  LDAP-connection shall use SSL or not and decides based on that which port to
  use if no port has been set.
- [zendframework/zend-ldap#25](https://github.com/zendframework/zend-ldap/issues/25) Check for correct
  Headers in the documentation and fix it
- [zendframework/zend-ldap#27](https://github.com/zendframework/zend-ldap/issues/27) Check for different
  issues in the documentation and fixed it
- [zendframework/zend-ldap#29](https://github.com/zendframework/zend-ldap/issues/29) Check for incorrect
  Blockquotes in the documentation and fix it


## 2.6.0 - 2016-02-11

### Added

- [zendframework/zend-ldap#6](https://github.com/zendframework/zend-ldap/pull/6) Adds a possibility 
  to delete attributes without having to remove the complete node and add it
  again without the attribute

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-ldap#16](https://github.com/zendframework/zend-ldap/pull/16) Fixed the usage of
  ```ldap_sort``` during sorting search-results due to deprecation of 
  ```ldap_sort``` in PHP 7

## 2.5.2 - 2016-02-11

### Added

- [zendframework/zend-ldap#16](https://github.com/zendframework/zend-ldap/pull/16) removes the call
  to the now deprecated ldap_sort-function wile still preserving the
  sort-functionality.
- [zendframework/zend-ldap#14](https://github.com/zendframework/zend-ldap/pull/14) adds a Vagrant
  environment for running an LDAP server against which to run the tests;
  additionally, it adds Travis-CI scripts for setting up an LDAP server with
  test data.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-ldap#18](https://github.com/zendframework/zend-ldap/pull/18) Fixes an already
  removed second parameter to ```ldap_first_attribute``` and ```ldap_next_attribute```
