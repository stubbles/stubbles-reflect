# Changelog

## 9.0.0 (2019-10-29)

* raised minimum required PHP version to 7.3
* fixed various potential bugs discovered by phpstan

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
