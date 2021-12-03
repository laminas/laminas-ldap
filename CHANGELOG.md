# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.12.0 - 2021-12-03


-----

### Release Notes for [2.12.0](https://github.com/laminas/laminas-ldap/milestone/4)

Feature release (minor)

### 2.12.0

- Total issues resolved: **1**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### Enhancement

 - [23: Added support for PHP 8.1](https://github.com/laminas/laminas-ldap/pull/23) thanks to @Koen1999 and @froschdesign
 - [18: Psalm integration](https://github.com/laminas/laminas-ldap/pull/18) thanks to @ghostwriter
 - [16: Remove file headers](https://github.com/laminas/laminas-ldap/pull/16) thanks to @ghostwriter

## 2.11.0 - 2021-01-12


-----

### Release Notes for [2.11.0](https://github.com/laminas/laminas-ldap/milestone/2)



### 2.11.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement

 - [13: Support PHP 7.3 7.4 8.0](https://github.com/laminas/laminas-ldap/pull/13) thanks to @phil-davis

## 2.10.3 - 2020-03-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed `replace` version constraint in composer.json so repository can be used as replacement of `zendframework/zend-ldap:^2.10.1`.

## 2.10.2 - 2020-03-15

### Added

- [#8](https://github.com/laminas/laminas-ldap/pull/8) adds support for PHP 7.4.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/laminas/laminas-ldap/pull/7) fixes compatibility with PHP 7.4 in `Collection\DefaultIterator`.

## 2.10.1 - 2019-10-17

### Added

- [zendframework/zend-ldap#82](https://github.com/zendframework/zend-ldap/pull/82) adds support for PHP 7.3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.10.0 - 2018-07-05

### Added

- [zendframework/zend-ldap#64](https://github.com/zendframework/zend-ldap/pull/64) Adds support for SASL-Bind - Thanks to @mbaynton
- [zendframework/zend-ldap#66](https://github.com/zendframework/zend-ldap/pull/66) Adds support for automatic reconnection - Thanks to @mbaynton
- [zendframework/zend-ldap#73](https://github.com/zendframework/zend-ldap/pull/73) Adds support for modifying attributes - Thanks to @glimac and @mbaynton

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-ldap#69](https://github.com/zendframework/zend-ldap/pull/69) Drop support for OpenLDAP < 2.2 due to using ldap-URI exclusively - Thanks to @fduch

### Fixed

- [zendframework/zend-ldap#51](https://github.com/zendframework/zend-ldap/issues/51) Use ldap_escape to escape values instead of own function - Thanks to @KaelBaldwin

## 2.9.0 - 2018-04-25

### Added

- [zendframework/zend-ldap#78](https://github.com/zendframework/zend-ldap/pull/78) Added support for PHP 7.2
- [zendframework/zend-ldap#60](https://github.com/zendframework/zend-ldap/pull/60) Adds tests for nightly PHP-builds

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-ldap#61](https://github.com/zendframework/zend-ldap/pull/61) Removed support for PHP 5.5.
- [zendframework/zend-ldap#78](https://github.com/zendframework/zend-ldap/pull/78) Removed support for HHVM.

### Fixed

- [zendframework/zend-ldap#71](https://github.com/zendframework/zend-ldap/pull/71) Removes composer-flag ```--ignore-platform-deps``` to fix Travis-CI build
- [zendframework/zend-ldap#77](https://github.com/zendframework/zend-ldap/pull/77) Fixes links to PR in CHANGELOG.md)
- [zendframework/zend-ldap#78](https://github.com/zendframework/zend-ldap/pull/78) Updated Location for docs.
- [zendframework/zend-ldap#78](https://github.com/zendframework/zend-ldap/pull/78) Updated PHPUnit.

## 2.8.0 - 2017-03-06

### Added

- [zendframework/zend-ldap#53](https://github.com/zendframework/zend-ldap/pull/53) Adds addAttribute-method
to Ldap-class
- [zendframework/zend-ldap#57](https://github.com/zendframework/zend-ldap/pull/57) adds support for new
coding-standards.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

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
- [zendframework/zend-ldap#44](https://github.com/zendframework/zend-ldap/pull/44) Fixes broken builds
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

- [zendframework/zend-ldap#20](https://github.com/zendframework/zend-ldap/pull/20) checks whether the
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
