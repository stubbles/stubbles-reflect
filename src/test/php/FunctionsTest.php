<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\reflect
 */
namespace stubbles\reflect;
use function bovigo\assert\{
    assert,
    assertTrue,
    expect,
    predicate\each,
    predicate\equals,
    predicate\isGreaterThan,
    predicate\isInstanceOf
};
/**
 * Helper interface for the test.
 */
interface SomethingToReflect
{
    function something();
}
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
     */
    public function annotationsWithUnknownClassAndFunctionNameThrowsReflectionException()
    {
        expect(function() {
                annotationsOf('doesNotExist');
        })
        ->throws(\ReflectionException::class);
    }

    /**
     * @param  string  $refParam
     */
    private function example(string $refParam)
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

    public function properties(): array
    {
        return [['->', 'someProperty'], ['::$', 'otherProperty']];
    }

    /**
     * @test
     * @dataProvider  properties
     */
    public function annotationsWithReflectionPropertyReturnsPropertyAnnotations(
            string $connector,
            string $propertyName
    ) {
        $refProperty = new \ReflectionProperty($this, $propertyName);
        assert(
                annotationsOf($refProperty)->target(),
                equals(__CLASS__ . $connector . $propertyName)
        );
    }

    /**
     * @test
     */
    public function annotationTargetThrowsReflectionExceptionForNonSupportedAnnotationPlaces()
    {
        expect(function() {
                _annotationTarget(new \ReflectionExtension('date'));
        })
        ->throws(\ReflectionException::class);
    }

    /**
     * @test
     */
    public function docCommentThrowsReflectionExceptionForNonSupportedAnnotationPlaces()
    {
        expect(function() {
                docComment(new \ReflectionExtension('date'));
        })
        ->throws(\ReflectionException::class);
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
     */
    public function methodsWithNonClassThrowsInvalidArgumentException()
    {
        expect(function() {
                methodsOf(404);
        })
        ->throws(\InvalidArgumentException::class);
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
     */
    public function propertiesOfWithNonClassThrowsInvalidArgumentException()
    {
        expect(function() {
                propertiesOf(404);
        })
        ->throws(\InvalidArgumentException::class);
    }

    public function argumentsForParametersOf(): array
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
     */
    public function parametersOfWithNonParametersReferenceThrowsInvalidArgumentException()
    {
        expect(function() {
                parametersOf(404);
        })
        ->throws(\InvalidArgumentException::class);
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
     * @since  4.0.0
     */
    public function reflectWithUnknownClassAndFunctionNameThrowsReflectionException()
    {
        expect(function() {
                reflect('doesNotExist');
        })
        ->throws(\ReflectionException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectInterface()
    {
        assert(reflect(SomethingToReflect::class), isInstanceOf(\ReflectionClass::class));
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
     * @dataProvider  invalidValues
     * @since  4.0.0
     */
    public function reflectInvalidValueThrowsIllegalArgumentException($invalidValue)
    {
        expect(function() use ($invalidValue) {
                reflect($invalidValue);
        })
        ->throws(\InvalidArgumentException::class);
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
