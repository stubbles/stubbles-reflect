stubbles/reflect
=================

Reflection helper functions and annotations.


Build status
------------

![Tests](https://github.com/stubbles/stubbles-reflect/workflows/Tests/badge.svg)

[![Latest Stable Version](https://poser.pugx.org/stubbles/reflect/version.png)](https://packagist.org/packages/stubbles/reflect) [![Latest Unstable Version](https://poser.pugx.org/stubbles/reflect/v/unstable.png)](//packagist.org/packages/stubbles/reflect)


Installation
------------

_stubbles/reflect_ is distributed as [Composer](https://getcomposer.org/)
package. To install it as a dependency of your package use the following
command:

    composer require "stubbles/reflect": "^10.0"


Requirements
------------

_stubbles/reflect_ requires at least PHP 8.2.

Additionally it uses _[stubbles/sequence](https://github.com/stubbles/stubbles-sequence)_
to return sequences from some of the functions, and
_[stubbles/values](https://github.com/stubbles/stubbles-values)_ to parse values
from annotations.


Reflection helper functions
---------------------------

All functions are in namespace `stubbles\reflect`.

### `reflect()`

_Available since release 3.1.0_

To provide more convenience a function `stubbles\reflect\reflect()` is provided.
It allows to reflect classes, objects and methods:

```php
$refClass  = reflect('some\interesting\UserDefinedClass'); // creates instance of \ReflectionClass
$refObject = reflect($someObjectInstance); // creates instance of \ReflectionObject
$refMethod = reflect('some\interesting\UserDefinedClass', 'aMethod'); // creates instance of \ReflectionMethod
$refMethod = reflect($someObjectInstance, 'aMethod'); // same as line before
```

Since release 4.0.0, it also allows to reflect functions:
```php
$refFunction = reflect('someFunction'); // creates instance of \ReflectionFunction
````


### `reflectConstructor(): \ReflectionMethod`

Shortcut for `reflect($someObject, '__construct')`

```php
$refMethod =  reflectConstructor('some\interesting\UserDefinedClass'); // same as reflect('some\interesting\UserDefinedClass', '__construct');
$refMethod =  reflectConstructor($someObjectInstance); // same as reflect('some\interesting\UserDefinedClass', '__construct');
```

### `methodsOf($class, $filter = null): \stubbles\sequence\Sequence`

Returns a sequence of all methods of given class.

```php

```


### `propertiesOf($class, $filter = null): \stubbles\sequence\Sequence`

Returns a sequence of all properties of given class.

```php

```


### `parametersOf($classOrFunction, $methodName = null): \stubbles\sequence\Sequence`

Returns a sequence of all properties of given class.

```php

```


### `parametersOfConstructor($class): \stubbles\sequence\Sequence`

Shortcut for `parametersOf($classOrFunction, '__construct')`.


### `parameter($name, $classOrFunction, $methodName = null): \ReflectionParameter`

Returns parameter with given name from referenced function or method.

```php

```


### `constructorParameter($name, $class): \ReflectionParameter`

Shortcut for `parameter($name, $class, '__construct')`.


Annotations
-----------

For details about the concept behind annotations see
[annotations](http://en.wikipedia.org/wiki/Annotations) which contains a general
introduction into the topic. For more details how to use this in a programming
language see [Java annotations](http://en.wikipedia.org/wiki/Java_annotation).

### How to define an annotation

Annotations can be defined on every element that can get a docblock comment:
functions, methods, classes and properties. Additionally it is possible to
define annotations for parameters in the docblock comment of the function or
method where the parameter is part of.

```php
namespace my;
/**
 * Class to demonstrate how to define annotations
 *
 * @MyAnnotation
 */
class ExampleClass
{
    /**
     * an example property
     *
     * @var  string
     * @AnnotationWithValues(bar='dummy', baz=42, required=true)
     */
    protected $bar;

    /**
     * another example property
     *
     * @var  string
     * @AnnotationWithOneValue("anotherDummy")
     */
    protected $baz;

    /**
     * an example method
     *
     * @param  int  $param  a parameter
     * @CastedAnnotation[MyAnnotation]
     * @ParamAnnotation{param}(key='value')
     */
    public function aMethod($param)
    {
        // some code here
    }
}
```

In the above example you can see five different ways of defining an annotation.
However, you can combine these ways as you like. You may have a casted
annotation with no, one or more values. But lets go through all defined
annotations.

 * **_@MyAnnotation_**
   This is an annotation without any value.
 * **_@AnnotationWithValues(bar='dummy', baz=42, required=true)_**
   This annotation has two values with the parameters bar, baz and required. One is
   a string, the other is an integer, and the last a boolean.
 * **_@AnnotationWithOneValue("anotherDummy")_**
   This annotation has only a single string value.
 * **_@CastedAnnotation[MyAnnotation]_**
   A casted annotation can be used distinguish between a marker and a hint which
   concrete actions the annotation consuming class should do. The first name is
   the marker, and the name part in square brackets denotes the action to follow.
 * **_@ParamAnnotation{param}(key='value')_**
   This annotation is a parameter annotation defined for the parameter `$param`
   of the method `aMethod()`. The name in the curly braces denotes the name of
   the parameter this annotation is for. Please be aware that this annotation is
   not available when retrieving it via `annotationsOf('Foo', 'aMethod')`, this
   will result in a `ReflectionException`.

In contrast to other implementations it is not required to create a separate
class for the annotation.


### Read annotations

To get a list of all annotations of an annotatable element call the
`stubbles\reflect\annotationsOf()` function. It returns an instance of
`stubbles\reflect\annotation\Annotations` and supports the following invocations:

```php
annotationsOf('my\ExampleClass', 'aMethod'); // returns annotations of this method
annotationsOf($exampleInstance, 'aMethod'); // returns annotations of this method
annotationsOf('my\ExampleClass'); // returns annotations of this class
annotationsOf($exampleInstance); // returns annotations of this class
annotationsOf('my\examplefunction'); // returns annotations of this function
annotationsOf($reflectionParameter); // returns annotations of this parameter
annotationsOf($reflectionProperty); // returns annotations of this class property
```

As convienience function to retrieve annotations of a class' constructor the
function `stubbles\reflect\annotationsOfConstructorParameter()` can be used:

```php
annotationsOfConstructorParameter('my\ExampleClass');
annotationsOfConstructorParameter($exampleInstance);
```

As convienience function to retrieve annotations of a function or method
parameter the function 'stubbles\reflect\annotationsOfParameter()` can be used:

```php
annotationsOfParameter('param', 'my\ExampleClass', 'aMethod');
annotationsOfParameter('param', $exampleInstance, 'aMethod');
annotationsOfParameter('param', 'someFunction');
```

### Read values from annotations

As seen above annotations can have values. If we take the `@AnnotationWithValues`
from the class above this is how you can access them:

```php
$annotation = annotationsOf(/** reflectable annotation source */);
echo $annotation->getBar(); // prints "dummy"
echo $annotation->bar; // prints "dummy"
echo $annotation->getValueByName('bar'); // prints "dummy"
if ($annotation->isRequired()) {
    echo 'Required!';
}
```

These are the possibilities:
* Boolean values can be retrieved using the method `is` followed by their name.
* Any other value types can be retrieved using the method `get` followed by
  their name, as property of the annotation class, or using the `getValueByName()`
  method. _Please note that boolean values can also be retrieved using the `get`
  method syntax, but most times it just doesn't look that good: `getRequired()`
  has a different connotation than `isRequired()` from the point of view of the
  reader of your code._

In case the annotation has an unnamed value only it can be retrieved using
`$annotation->value` or `$annotation->getValue()`.

Using the method syntax with `get` has the advantage that a default value can be
supplied which will be returned in case a value with this name is not set:

```php
echo $annotation->getAwesomeness('Roland TB-303'); // prints "Roland TB-303"
```

The default value for the `is` method syntax is `false`.

If values are optional their existence can be checked:
`$annotation->hasValueByName('bar')` returns `true` if a value with the name
_bar_ is set, and `false` otherwise.

When parameter values are read they are parsed using `stubbles\values\Parse`
from _[stubbles/values](https://github.com/stubbles/stubbles-values)_. See
documentation of this package how values are parsed.


### Annotation cache

As parsing annotations is quite expensive Stubbles will cache annotations once
they are read. However, the cache by default works only on a per request base
and is not persistent. In order to make the annotation cache persistent between
different requests the application needs to provide an annotation cache
persistence.

The simplest way to do so is to call
`stubbles\reflect\annotation\persistAnnotationsInFile('/path/to/some/cachefile.cache')`
which will then use the file named in the argument to persist the annotation
cache inbetween requests.

If a cache file doesn't suit your needs you can also provide a different
persistence. To do so, you need to call
`stubbles\reflect\annotation\persistAnnotations()` and provide two functions,
one which is able to read the data and return it, and another one which can
receive data and store it. For a file cache implementation, this would look like
this:

```php
persistAnnotations(
        function() use($cacheFile)
        {
            if (file_exists($cacheFile)) {
                return unserialize(file_get_contents($cacheFile));
            }

            return [];
        },
        function(array $annotationData) use($cacheFile)
        {
            file_put_contents($cacheFile, serialize($annotationData));
        }
);
```
The first function must return the stored annotation data. If no such data is
present it must return an empty array.

The second function must store passed annotation data.

**Please note that if you use a persistent annotation cache that you need to be
able to clear this cache, as changes in your code on annotations will not lead
to an update of the cached data. This might lead to confusion on why a changed
annotation will not be respected when the code is executed.**
