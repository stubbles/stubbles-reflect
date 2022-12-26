<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect {
    use stubbles\sequence\Sequence;
    use stubbles\reflect\annotation\AnnotationCache;
    use stubbles\reflect\annotation\Annotations;

    use function stubbles\values\typeOf;

    /**
     * reflects given input and returns an appropriate reflector
     *
     * If no method name is provided it will check whether $class denotes a
     * class name or is an object instance. If yes it returns an instance of
     * \ReflectionClass or \ReflectionObject which allows reflection on the
     * provided class.
     * In case $class is a function name it will return a
     * \ReflectionFunction which allows reflection on the
     * function.
     * In case a method name is provided it will return an instance of
     * \ReflectionMethod which allows reflection on the
     * specific method.
     *
     * @template T of object
     * @param   class-string<T>|T|callable  $class       class name, function name of or object instance to reflect
     * @param   string                      $methodName  optional  specific method to reflect when first parameter refers to a class
     * @return  \ReflectionClass<T>|\ReflectionObject<T>|\ReflectionMethod|\ReflectionFunction
     * @throws  \ReflectionException
     * @throws  \InvalidArgumentException
     * @since   3.1.0
     * @api
     */
    function reflect($class, string $methodName = null): \Reflector
    {
        if (\is_callable($class) && \is_string($class)) {
            return new \ReflectionFunction($class);
        }

        if (\is_array($class) && count($class) === 2) {
            list($class, $methodName) = $class;
        }

        if (\is_object($class)) {
            if (null != $methodName) {
                return new \ReflectionMethod($class, $methodName);
            }

            return new \ReflectionObject($class);
        }

        if (\is_string($class) && (\class_exists($class) || \interface_exists($class))) {
            /** @var  class-string<T>  $class */
            if (null != $methodName) {
                return new \ReflectionMethod($class, $methodName);
            }

            return new \ReflectionClass($class);
        }

        if (\is_string($class)) {
            throw new \ReflectionException('Given function or class "' . $class . '" does not exist');
        }

        throw new \InvalidArgumentException(
                'Given class must either be a function name,'
                . ' class name or class instance, or callable; but '
                . typeOf($class) . ' given'
        );
    }

    /**
     * shortcut for reflect($class, '__construct')
     *
     * @template T of object
     * @param   class-string<T>|T  $class  class name of or object instance to reflect constructor of
     * @return  \ReflectionMethod
     * @since   3.1.0
     * @api
     */
    function reflectConstructor($class): \ReflectionMethod
    {
        /** @var \ReflectionMethod $constructor */
        $constructor = reflect($class, '__construct');
        return $constructor;
    }

    /**
     * returns annotations for given reflected
     *
     * @template T of object
     * @param   \Reflector|class-string<T>|T|callable  $reflected   class name, function name of or object instance to reflect
     * @param   string                             $methodName  optional  method to reflect when first parameter refers to a class
     * @return  \stubbles\reflect\annotation\Annotations
     * @since   5.3.0
     */
    function annotationsOf($reflected, string $methodName = null): Annotations
    {
        $reflector = ($reflected instanceof \Reflector) ? $reflected : reflect($reflected, $methodName);
        $target    = _annotationTarget($reflector);
        if (AnnotationCache::has($target)) {
            return AnnotationCache::get($target);
        }

        list($sourceTarget) = explode('#', $target);
        $return = null;
        foreach (Annotations::parse(docComment($reflector), $sourceTarget) as $annotations) {
            AnnotationCache::put($annotations);
            if ($annotations->target() === $target) {
                $return = $annotations;
            }
        }

        if (null === $return) {
            $return = new Annotations($target);
            AnnotationCache::put($return);
        }

        return $return;
    }

    /**
     * returns annotations of constructor of given reflected
     *
     * @template T of object
     * @param   \ReflectionClass<T>|class-string<T>|T  $reflected   class name, class instance of or object instance to reflect constructor annotations of
     * @return  \stubbles\reflect\annotation\Annotations
     * @throws  \ReflectionException  when class doesn't have a constructor
     * @since   5.3.0
     */
    function annotationsOfConstructor($reflected): Annotations
    {
        if ($reflected instanceof \ReflectionClass) {
            $constructor = $reflected->getConstructor();
            if (null === $constructor) {
                throw new \ReflectionException('Method ' . $reflected->getName() . '::__construct() does not exist');
            }
        } else {
            $constructor = reflectConstructor($reflected);
        }

        return annotationsOf($constructor);
    }

    /**
     * returns annotations for given parameter
     *
     * @template T of object
     * @param   string                                                  $name             name of parameter to retrieve annotations for
     * @param   class-string<T>|T|callable|\ReflectionFunctionAbstract  $classOrFunction  something that references a function or a class
     * @param   string                                                  $methodName       optional  in case first param references a class
     * @return  \stubbles\reflect\annotation\Annotations
     * @since   5.3.0
     */
    function annotationsOfParameter(string $name, $classOrFunction, string $methodName = null): Annotations
    {
        return annotationsOf(parameter($name, $classOrFunction, $methodName));
    }

    /**
     * retrieves parameter with given name from referenced function or method
     *
     * @template T of object
     * @param   string                                         $name             name of parameter to retrieve
     * @param   class-string<T>|T|\ReflectionFunctionAbstract  $classOrFunction  something that references a function or a class
     * @return  \stubbles\reflect\annotation\Annotations
     * @since   5.3.0
     */
    function annotationsOfConstructorParameter(string $name, $classOrFunction): Annotations
    {
        return annotationsOf(constructorParameter($name, $classOrFunction));
    }

    /**
     * returns annotation target for given reflector
     *
     * @internal
     * @param   \Reflector $reflector
     * @return  string
     * @throws  \ReflectionException
     * @since   5.3.0
     */
    function _annotationTarget(\Reflector $reflector): string
    {
        if ($reflector instanceof \ReflectionClass) {
            return $reflector->getName();
        }

        if ($reflector instanceof \ReflectionMethod) {
            return $reflector->class . '::' . $reflector->getName() . '()';
        }

        if ($reflector instanceof \ReflectionFunction) {
            return $reflector->getName() . '()';
        }

        if ($reflector instanceof \ReflectionParameter) {
            return _annotationTarget($reflector->getDeclaringFunction()) . '#' . $reflector->getName();
        }

        if ($reflector instanceof \ReflectionProperty) {
            return $reflector->class . ($reflector->isStatic() ? '::$' : '->') . $reflector->getName();
        }

        throw new \ReflectionException('Can not retrieve target for ' . get_class($reflector));
    }

    /**
     * returns doc comment for given reflector
     *
     * @internal
     * @param   \Reflector  $reflector
     * @return  string
     * @throws  \ReflectionException
     * @since   5.3.0
     */
    function docComment(\Reflector $reflector): string
    {
        if ($reflector instanceof \ReflectionClass
                || $reflector instanceof \ReflectionFunctionAbstract
                || $reflector instanceof \ReflectionProperty) {
            $docComment = $reflector->getDocComment();
            return (false !== $docComment ? $docComment : '');
        }

        if ($reflector instanceof \ReflectionParameter) {
            return docComment($reflector->getDeclaringFunction());
        }

        throw new \ReflectionException('Can not retrieve doc comment for ' . get_class($reflector));
    }

    /**
     * returns a sequence of all methods of given class
     *
     * @template T of object
     * @param   class-string<T>|T|\ReflectionClass<T>  $class   class to return methods for
     * @param   int                                    $filter  optional  filter the results to include only methods with certain attributes using any combination of ReflectionMethod::IS_ constants
     * @return  \stubbles\sequence\Sequence<\ReflectionMethod>
     * @throws  \InvalidArgumentException
     * @since   5.3.0
     */
    function methodsOf($class, int $filter = null): Sequence
    {
        if (!($class instanceof \ReflectionClass)) {
            $class = reflect($class);
            if (!($class instanceof \ReflectionClass)) {
                throw new \InvalidArgumentException(
                    'Given class must be a class name, a class instance'
                    . ' or an instance of \ReflectionClass'
                );
            }
        }

        return Sequence::of(
                null === $filter ? $class->getMethods() : $class->getMethods($filter)
        )->mapKeys(
                function($key, \ReflectionMethod $method = null)
                {
                    if (null === $method) { return $key; }
                    return $method->getName();
                }
        );
    }

    /**
     * returns a sequence of all properties of given class
     *
     * @template T of object
     * @param   class-string<T>|T|\ReflectionClass<T>  $class   class to return properties for
     * @param   int                                    $filter  optional  filter the results to include only properties with certain attributes using any combination of ReflectionProperty::IS_ constants
     * @return  \stubbles\sequence\Sequence<\ReflectionProperty>
     * @throws  \InvalidArgumentException
     * @since   5.3.0
     */
    function propertiesOf($class, int $filter = null): Sequence
    {
        if (!($class instanceof \ReflectionClass)) {
            $class = reflect($class);
            if (!($class instanceof \ReflectionClass)) {
                throw new \InvalidArgumentException(
                        'Given class must be a class name, a class instance'
                        . ' or an instance of \ReflectionClass'
                );
            }
        }

        return Sequence::of(
                null === $filter ? $class->getProperties() : $class->getProperties($filter)
        )->mapKeys(
                function($key, \ReflectionProperty $property)
                {
                    return $property->getName();
                }
        );
    }

    /**
     * returns sequence of parameters of a function or method
     *
     * @template T of object
     * @param   class-string<T>|T|\ReflectionClass<T>|callable|\ReflectionFunctionAbstract  $classOrFunction  something that references a function or a class
     * @param   string                                                                      $methodName       optional  name of method in case first param references a class
     * @return  \stubbles\sequence\Sequence<\ReflectionParameter>
     * @throws  \InvalidArgumentException
     * @since   5.3.0
     */
    function parametersOf($classOrFunction, string $methodName = null): Sequence
    {
        if (!($classOrFunction instanceof \ReflectionFunctionAbstract)) {
            $function = reflect($classOrFunction, $methodName);
            if (!($function instanceof \ReflectionFunctionAbstract)) {
                throw new \InvalidArgumentException(
                        'Given function must be a function name, a method'
                        . ' reference or an instance of \ReflectionFunctionAbstract'
                );
            }
        } else {
            $function = $classOrFunction;
        }

        return Sequence::of($function->getParameters())
                ->mapKeys(
                function($key, \ReflectionParameter $parameter)
                {
                    return $parameter->getName();
                }
        );
    }

    /**
     * returns constructor parameters for given class
     *
     * @template T of object
     * @param   class-string<T>|T|\ReflectionClass<T>  $class  something that references a function or a class
     * @return  \stubbles\sequence\Sequence<\ReflectionParameter>
     * @since   5.3.0
     */
    function parametersOfConstructor($class): Sequence
    {
        return parametersOf($class, '__construct');
    }

    /**
     * retrieves parameter with given name from referenced function or method
     *
     * @template T of object
     * @param   string                                              $name             name of parameter to retrieve
     * @param   class-string<T>|T|\ReflectionClass<T>|callable|\ReflectionFunctionAbstract  $classOrFunction  something that references a function or a class
     * @param   string                                              $methodName       optional  in case first param references a class
     * @return  \ReflectionParameter
     * @since   5.3.0
     */
    function parameter(string $name, $classOrFunction, string $methodName = null): \ReflectionParameter
    {
        return parametersOf($classOrFunction, $methodName)
                ->filter(
                        function(\ReflectionParameter $parameter) use ($name)
                        {
                            return $parameter->getName() === $name;
                        }
        )->first();
    }

    /**
     * retrieves parameter with given name from constructor of referenced class
     *
     * @template T of object
     * @param   string                                 $name   name of parameter to retrieve
     * @param   class-string<T>|T|\ReflectionClass<T>  $class  something that references a function or a class
     * @return  \ReflectionParameter
     * @since   5.3.0
     */
    function constructorParameter(string $name, $class): \ReflectionParameter
    {
        return parameter($name, $class, '__construct');
    }
}
namespace stubbles\reflect\annotation {

    /**
     * enable persistent annotation cache with given cache storage logic
     *
     * The $readCache closure must return the stored annotation data. If no such
     * data is present it must return null. In case the stored annotation data
     * can't be unserialized into an array a \RuntimeException will be thrown.
     *
     * The $storeCache closure must store passed annotation data. It doesn't
     * need to take care about serialization, as it already receives a
     * serialized representation.
     *
     * A possible implementation for the file cache would look like this:
     * <code>
     * self::persistAnnotations(
     *     function() use($cacheFile)
     *     {
     *          if (file_exists($cacheFile)) {
     *              return file_get_contents($cacheFile);
     *          }
     *
     *          return null;
     *      },
     *      function($annotationData) use($cacheFile)
     *      {
     *          file_put_contents($cacheFile, $annotationData);
     *      }
     * );
     * </code>
     *
     * @param  callable(): array<string,string>      $readCache
     * @param  callable(array<string,string>): void  $storeCache
     * @since  3.1.0
     * @api
     */
    function persistAnnotations(callable $readCache, callable $storeCache): void
    {
        AnnotationCache::start($readCache, $storeCache);
    }

    /**
     * enable persistent annotation cache by telling where to store cache data
     *
     * @param  string  $cacheFile
     * @since  3.1.0
     * @api
     */
    function persistAnnotationsInFile($cacheFile): void
    {
        AnnotationCache::startFromFileCache($cacheFile);
    }
}
