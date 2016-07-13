8.0.0 (2016-07-??)
------------------

### BC breaks

  * raised minimum required PHP version to 7.0.0
  * introduced scalar type hints and strict type checking
  * `stubbles\reflect\annotation\Annotations::all()` now returns a `\Generator` instead of an `array`
  * parameter annotations with empty parameter name like `@Foo{}` are now invalid and throw a `\ReflectionException`


7.0.0 (2016-01-11)
------------------

  * split off from [stubbles/core](https://github.com/stubbles/stubbles-core)


### BC breaks

  * parsing enums as annotation value is not supported any more
