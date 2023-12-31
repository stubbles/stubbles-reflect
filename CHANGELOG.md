# Changelog

## 10.0.0 (2022-12-??)

* raised minimum required PHP version to 8.2

## 9.2.0 (2020-03-02)

* added new method `stubbles\reflect\annotation\Annotations::count()` so that the amount of annotations can be counted, implementing the  `\Countable` interface
* unified behavior of `stubbles\reflect\annotationsOfConstructor()`: now always throws a `\ReflectionException` when class doesn't have a constructor, independent of whether argument is a class name, an instance, or an instance of `\ReflectionClass`

## 9.1.0 (2019-12-11)

* added more phpstan related type hints

## 9.0.1 (2019-11-18)

* replace `array` in allowed values of parameters in functions with `callable`
  * `stubbles\reflect\reflect()`
  * `stubbles\reflect\annotationsOf()`
  * `stubbles\reflect\annotationsOfParameter()`
  * `stubbles\reflect\parametersOf()`
  * `stubbles\reflect\parameter()`

## 9.0.0 (2019-10-29)

* raised minimum required PHP version to 7.3
* fixed various potential bugs discovered by phpstan
* fixed curly brace syntax to be forward compatible with PHP 7.4

## 8.0.2 (2016-08-01)

* fixed bug where indented annotation parameter names were not parsed correct

## 8.0.1 (2016-07-21)

* fixed bug with `stubbles\reflect\docComment()` trying to return `false` when requested doc comment does not exist, resulting in a `\TypeError`, now returns empty string in such cases

## 8.0.0 (2016-07-17)

### BC breaks

* raised minimum required PHP version to 7.0.0
* introduced scalar type hints and strict type checking
* `stubbles\reflect\annotation\Annotations::all()` now returns a `\Generator` instead of an `array`
* parameter annotations with empty parameter name like `@Foo{}` are now invalid and throw a `\ReflectionException`
* empty refined annotation types like `@Foo[]` are now invalid and throw a `\ReflectionException`

### Other changes

* improved performance for annotation parser by 100%
* provided more context in exception messages when annotation contains errors

## 7.0.0 (2016-01-11)

* split off from [stubbles/core](https://github.com/stubbles/stubbles-core)

### BC breaks

* parsing enums as annotation value is not supported any more
