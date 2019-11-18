<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\{
    assertThat,
    assertEmptyString,
    assertTrue,
    expect,
    predicate\each,
    predicate\equals,
    predicate\isGreaterThan,
    predicate\isInstanceOf
};
/**
 * Tests for stubbles\reflect\*().
 *
 * @since  5.3.0
 * @group  reflect
 * @group  functions
 */
class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function annotationsWithMethodNameReturnsMethodAnnotations()
    {
        assertThat(
                annotationsOf(__CLASS__, __FUNCTION__)->target(),
                equals(__CLASS__ . '::' . __FUNCTION__ . '()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassNameReturnsClassAnnotations()
    {
        assertThat(annotationsOf(__CLASS__)->target(), equals(__CLASS__));
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassNameReturnsConstructorAnnotations()
    {
        assertThat(
                annotationsOfConstructor(__CLASS__)->target(),
                equals(TestCase::class . '::__construct()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassInstanceReturnsClassAnnotations()
    {
        assertThat(annotationsOf($this)->target(), equals(__CLASS__));
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassInstanceReturnsConstructorAnnotations()
    {
        assertThat(
                annotationsOfConstructor($this)->target(),
                equals(TestCase::class . '::__construct()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithFunctionNameReturnsFunctionAnnotations()
    {
        assertThat(
                annotationsOf('stubbles\reflect\annotationsOf')->target(),
                equals('stubbles\reflect\annotationsOf()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithUnknownClassAndFunctionNameThrowsReflectionException()
    {
        expect(function() { annotationsOf('doesNotExist'); })
            ->throws(\ReflectionException::class)
            ->withMessage('Given function or class "doesNotExist" does not exist');
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
        assertThat(
                annotationsOf($refParam)->target(),
                equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @test
     */
    public function annotationsOfParameterWithClassInstanceReturnsParameterAnnotations()
    {
        assertThat(
                annotationsOfParameter('refParam', $this, 'example')->target(),
                equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @test
     */
    public function annotationsOfParameterWithClassNameReturnsParameterAnnotations()
    {
        assertThat(
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
        assertThat(
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
     * @since  8.0.1
     */
    public function reflectorWithUndefinedDocComments(): array
    {
        $refMethod = new \ReflectionMethod(SomethingToReflect::class, 'something');
        return [
                [new \ReflectionClass('stdClass')],
                [$refMethod],
                [$refMethod->getParameters()[0]]
        ];
    }

    /**
     * @test
     * @dataProvider  reflectorWithUndefinedDocComments
     * @since  8.0.1
     */
    public function undefinedDocCommentIsEmpty(\Reflector $reflector)
    {
        assertEmptyString(docComment($reflector));
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
        assertThat(methodsOf($this)->count(), isGreaterThan(0));
    }

    /**
     * @test
     */
    public function allMethodsAreInstanceOfReflectionMethod()
    {
        assertThat(methodsOf($this), each(isInstanceOf(\ReflectionMethod::class)));
    }

    /**
     * @test
     */
    public function keyIsNameOfMethod()
    {
        // cast for phpstan :/
        $methodName = (string) key(methodsOf($this)->data());
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
        assertThat(propertiesOf($this)->count(), isGreaterThan(0));
    }

    /**
     * @test
     */
    public function allPropertiesAreInstanceOfReflectionProperty()
    {
        assertThat(propertiesOf($this), each(isInstanceOf(\ReflectionProperty::class)));
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
        assertThat(parametersOf(...$reflect)->count(), equals(1));
    }

    /**
     * @test
     * @dataProvider  argumentsForParametersOf
     */
    public function allParametersOfAreInstanceOfReflectionParameter(...$reflect)
    {
        assertThat(
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
        assertThat(
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
        assertThat(
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
        assertThat(
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
        assertThat(reflect(__CLASS__), isInstanceOf(\ReflectionClass::class));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectWithClassInstanceReturnsReflectionObject()
    {
        assertThat(reflect($this), isInstanceOf(\ReflectionObject::class));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectWithFunctionNameReturnsReflectionFunction()
    {
        assertThat(
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
        expect(function() { reflect('doesNotExist');})
            ->throws(\ReflectionException::class)
            ->withMessage('Given function or class "doesNotExist" does not exist');
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function reflectInterface()
    {
        assertThat(reflect(SomethingToReflect::class), isInstanceOf(\ReflectionClass::class));
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
        assertThat(
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
        assertThat(
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
        assertThat(
                reflect(function() { }),
                isInstanceOf(\ReflectionObject::class)
        );
    }
}
