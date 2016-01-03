<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\reflect
 */
namespace stubbles\reflect;
use stubbles\reflect\annotation\parser\AnnotationParser;
use function bovigo\assert\assert;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\each;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isGreaterThan;
use function bovigo\assert\predicate\isInstanceOf;
/**
 * Tests for stubbles\reflect\*().
 *
 * @since  5.3.0
 * @group  reflect
 * @group  functions
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function annotationsWithMethodNameReturnsMethodAnnotations()
    {
        assert(
                annotationsOf(__CLASS__, __FUNCTION__)->target(),
                equals(__CLASS__ . '::' . __FUNCTION__ . '()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassNameReturnsClassAnnotations()
    {
        assert(annotationsOf(__CLASS__)->target(), equals(__CLASS__));
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassNameReturnsConstructorAnnotations()
    {
        assert(
                annotationsOfConstructor(__CLASS__)->target(),
                equals('PHPUnit_Framework_TestCase::__construct()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassInstanceReturnsClassAnnotations()
    {
        assert(annotationsOf($this)->target(), equals(__CLASS__));
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassInstanceReturnsConstructorAnnotations()
    {
        assert(
                annotationsOfConstructor($this)->target(),
                equals('PHPUnit_Framework_TestCase::__construct()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithFunctionNameReturnsFunctionAnnotations()
    {
        assert(
                annotationsOf('stubbles\reflect\annotationsOf')->target(),
                equals('stubbles\reflect\annotationsOf()')
        );
    }

    /**
     * @test
     * @expectedException  ReflectionException
     */
    public function annotationsWithUnknownClassAndFunctionNameThrowsReflectionException()
    {
        annotationsOf('doesNotExist');
    }

    /**
     * @param  string  $refParam
     */
    private function example($refParam)
    {

    }

    /**
     * @test
     */
    public function annotationsWithReflectionParameterReturnsParameterAnnotations()
    {
        $refParam = new \ReflectionParameter([$this, 'example'], 'refParam');
        assert(
                annotationsOf($refParam)->target(),
                equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @test
     */
    public function annotationsOfParameterWithClassInstanceReturnsParameterAnnotations()
    {
        assert(
                annotationsOfParameter('refParam', $this, 'example')->target(),
                equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @test
     */
    public function annotationsOfParameterWithClassNameReturnsParameterAnnotations()
    {
        assert(
                annotationsOfParameter('refParam', __CLASS__, 'example')->target(),
                equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @type  null
     */
    private $someProperty = 303;
    /**
     *
     * @type  null
     */
    private static $otherProperty = 313;

    /**
     * @return  array
     */
    public function properties()
    {
        return [['->', 'someProperty'], ['::$', 'otherProperty']];
    }

    /**
     * @test
     * @dataProvider  properties
     */
    public function annotationsWithReflectionPropertyReturnsPropertyAnnotations($connector, $propertyName)
    {
        $refProperty = new \ReflectionProperty($this, $propertyName);
        assert(
                annotationsOf($refProperty)->target(),
                equals(__CLASS__ . $connector . $propertyName)
        );
    }

    /**
     * @test
     * @expectedException  ReflectionException
     */
    public function annotationTargetThrowsReflectionExceptionForNonSupportedAnnotationPlaces()
    {
        _annotationTarget(new \ReflectionExtension('date'));
    }

    /**
     * @test
     * @expectedException  ReflectionException
     */
    public function docCommentThrowsReflectionExceptionForNonSupportedAnnotationPlaces()
    {
        docComment(new \ReflectionExtension('date'));
    }

    /**
     * @test
     */
    public function methodsOfReturnsAllMethods()
    {
        assert(methodsOf($this)->count(), isGreaterThan(0));
    }

    /**
     * @test
     */
    public function allMethodsAreInstanceOfReflectionMethod()
    {
        assert(methodsOf($this), each(isInstanceOf(\ReflectionMethod::class)));
    }

    /**
     * @test
     */
    public function keyIsNameOfMethod()
    {
        $methodName = key(methodsOf($this)->data());
        assertTrue(method_exists($this, $methodName));
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     */
    public function methodsWithNonClassThrowsInvalidArgumentException()
    {
        methodsOf(404);
    }

    /**
     * @test
     */
    public function propertiesOfReturnsAllMethods()
    {
        assert(propertiesOf($this)->count(), isGreaterThan(0));
    }

    /**
     * @test
     */
    public function allPropertiesAreInstanceOfReflectionProperty()
    {
        assert(propertiesOf($this), each(isInstanceOf(\ReflectionProperty::class)));
    }

    /**
     * @test
     */
    public function keyIsNameOfProperty()
    {
        $propertyName = key(propertiesOf($this)->data());
        assertTrue(isset($this->$propertyName));
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     */
    public function propertiesOfWithNonClassThrowsInvalidArgumentException()
    {
        propertiesOf(404);
    }

    /**
     * @return  array
     */
    public function argumentsForParametersOf()
    {
        return [
            [$this, 'example'],
            [new \ReflectionMethod($this, 'example')]
        ];
    }

    /**
     * @test
     * @dataProvider  argumentsForParametersOf
     */
    public function parametersOfReturnsAllParameters(...$reflect)
    {
        assert(parametersOf(...$reflect)->count(), equals(1));
    }

    /**
     * @test
     * @dataProvider  argumentsForParametersOf
     */
    public function allParametersOfAreInstanceOfReflectionParameter(...$reflect)
    {
        assert(
                parametersOf(...$reflect),
                each(isInstanceOf(\ReflectionParameter::class))
        );
    }

    /**
     * @test
     * @dataProvider  argumentsForParametersOf
     */
    public function keyIsNameOfParameter(...$reflect)
    {
        assert(
                key(parametersOf(...$reflect)->data()),
                equals('refParam')
        );
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     */
    public function parametersOfWithNonParametersReferenceThrowsInvalidArgumentException()
    {
        parametersOf(404);
    }

    /**
     * @test
     */
    public function parameterReturnsExactReflectionParameter()
    {
        assert(
                parameter('refParam', $this, 'example')->getName(),
                equals('refParam')
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectWithMethodNameReturnsReflectionMethod()
    {
        assert(
                reflect(__CLASS__, __FUNCTION__),
                isInstanceOf(\ReflectionMethod::class)
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectWithClassNameReturnsReflectionClass()
    {
        assert(reflect(__CLASS__), isInstanceOf(\ReflectionClass::class));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectWithClassInstanceReturnsReflectionObject()
    {
        assert(reflect($this), isInstanceOf(\ReflectionObject::class));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectWithFunctionNameReturnsReflectionFunction()
    {
        assert(
                reflect('stubbles\reflect\reflect'),
                isInstanceOf(\ReflectionFunction::class)
        );
    }

    /**
     * @test
     * @expectedException  ReflectionException
     * @since  4.0.0
     */
    public function reflectWithUnknownClassAndFunctionNameThrowsReflectionException()
    {
        reflect('doesNotExist');
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectInterface()
    {
        assert(reflect(AnnotationParser::class), isInstanceOf(\ReflectionClass::class));
    }

    /**
     * @return  array
     */
    public static function invalidValues()
    {
        return [[404], [true], [4.04]];
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     * @dataProvider  invalidValues
     * @since  4.0.0
     */
    public function reflectInvalidValueThrowsIllegalArgumentException($invalidValue)
    {
        reflect($invalidValue);
    }

    /**
     * @test
     * @since  4.1.4
     */
    public function reflectCallbackWithInstanceReturnsReflectionMethod()
    {
        assert(
                reflect([$this, __FUNCTION__]),
                isInstanceOf(\ReflectionMethod::class)
        );
    }

    /**
     * @test
     * @since  4.1.4
     */
    public function reflectCallbackWithClassnameReturnsReflectionMethod()
    {
        assert(
                reflect([__CLASS__, __FUNCTION__]),
                isInstanceOf(\ReflectionMethod::class)
        );
    }

    /**
     * @test
     * @since  4.1.4
     */
    public function reflectClosureReturnsReflectionObject()
    {
        assert(
                reflect(function() { }),
                isInstanceOf(\ReflectionObject::class)
        );
    }
}
