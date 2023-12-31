<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionExtension;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
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
 * @since 5.3.0
 * @group reflect
 * @group functions
 */
class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function annotationsWithMethodNameReturnsMethodAnnotations(): void
    {
        assertThat(
            annotationsOf(__CLASS__, __FUNCTION__)->target(),
            equals(__CLASS__ . '::' . __FUNCTION__ . '()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassNameReturnsClassAnnotations(): void
    {
        assertThat(annotationsOf(__CLASS__)->target(), equals(__CLASS__));
    }

    public static function argumentVariantsOfClassWithoutConstructor(): Generator
    {
        yield 'class name' => [ClassWithoutConstructor::class];
        yield 'class instance' => [new ClassWithoutConstructor()];
        yield 'reflection instance' => [new ReflectionClass(ClassWithoutConstructor::class)];
    }

    /**
     * @test
     * @dataProvider argumentVariantsOfClassWithoutConstructor
     * @param class-string|object|ReflectionClass $toReflect
     */
    public function constructorAnnotationsForClassWithoutConstructorThrowsReflectionException(
        string|object $toReflect
    ): void {
        expect(fn() => annotationsOfConstructor($toReflect))
            ->throws(\ReflectionException::class)
            ->withMessage('Method stubbles\reflect\ClassWithoutConstructor::__construct() does not exist');
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassNameReturnsConstructorAnnotations(): void
    {
        assertThat(
            annotationsOfConstructor(__CLASS__)->target(),
            equals(TestCase::class . '::__construct()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassInstanceReturnsClassAnnotations(): void
    {
        assertThat(annotationsOf($this)->target(), equals(__CLASS__));
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassInstanceReturnsConstructorAnnotations(): void
    {
        assertThat(
                annotationsOfConstructor($this)->target(),
                equals(TestCase::class . '::__construct()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithFunctionNameReturnsFunctionAnnotations(): void
    {
        assertThat(
                annotationsOf('stubbles\reflect\annotationsOf')->target(),
                equals('stubbles\reflect\annotationsOf()')
        );
    }

    /**
     * @test
     */
    public function annotationsWithUnknownClassAndFunctionNameThrowsReflectionException(): void
    {
        expect(function() { annotationsOf('doesNotExist'); })
            ->throws(\ReflectionException::class)
            ->withMessage('Given function or class "doesNotExist" does not exist');
    }

    /**
     * @param  string  $refParam
     */
    private function example(string $refParam): void
    {

    }

    /**
     * @test
     */
    public function annotationsWithReflectionParameterReturnsParameterAnnotations(): void
    {
        $refParam = new ReflectionParameter([$this, 'example'], 'refParam');
        assertThat(
            annotationsOf($refParam)->target(),
            equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @test
     */
    public function annotationsOfParameterWithClassInstanceReturnsParameterAnnotations(): void
    {
        assertThat(
            annotationsOfParameter('refParam', $this, 'example')->target(),
            equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @test
     */
    public function annotationsOfParameterWithClassNameReturnsParameterAnnotations(): void
    {
        assertThat(
            annotationsOfParameter('refParam', __CLASS__, 'example')->target(),
            equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @test
     * @since 9.2.0
     */
    public function annotationsOfConstructorParameterWithClassNameReturnsParameterAnnotations(): void
    {
        assertThat(
            annotationsOfConstructorParameter('example', ClassWithConstructor::class)->target(),
            equals(ClassWithConstructor::class . '::__construct()#example')
        );
    }

    /**
     * @var int|null
     */
    private $someProperty = 303;
    /**
     *
     * @var int|null
     */
    private static $otherProperty = 313;

    /**
     * @return array<array<string>>
     */
    public static function properties(): array
    {
        return [['->', 'someProperty'], ['::$', 'otherProperty']];
    }

    /**
     * @test
     * @dataProvider properties
     */
    public function annotationsWithReflectionPropertyReturnsPropertyAnnotations(
        string $connector,
        string $propertyName
    ): void {
        $refProperty = new \ReflectionProperty($this, $propertyName);
        assertThat(
            annotationsOf($refProperty)->target(),
            equals(__CLASS__ . $connector . $propertyName)
        );
    }

    /**
     * @test
     */
    public function annotationTargetThrowsReflectionExceptionForNonSupportedAnnotationPlaces(): void
    {
        expect(fn() => _annotationTarget(new ReflectionExtension('date')))
            ->throws(ReflectionException::class);
    }

    /**
     * @since  8.0.1
     */
    public static function reflectorWithUndefinedDocComments(): Generator
    {
        $refMethod = new ReflectionMethod(SomethingToReflect::class, 'something');
        yield [new ReflectionClass('stdClass')];
        yield [$refMethod];
        yield [$refMethod->getParameters()[0]];
    }

    /**
     * @test
     * @dataProvider reflectorWithUndefinedDocComments
     * @since 8.0.1
     */
    public function undefinedDocCommentIsEmpty(Reflector $reflector): void
    {
        assertEmptyString(docComment($reflector));
    }

    /**
     * @test
     */
    public function docCommentThrowsReflectionExceptionForNonSupportedAnnotationPlaces(): void
    {
        expect(fn() => docComment(new ReflectionExtension('date')))
            ->throws(ReflectionException::class);
    }

    /**
     * @test
     */
    public function methodsOfReturnsAllMethods(): void
    {
        assertThat(methodsOf($this)->count(), isGreaterThan(0));
    }

    /**
     * @test
     */
    public function allMethodsAreInstanceOfReflectionMethod(): void
    {
        assertThat(methodsOf($this), each(isInstanceOf(ReflectionMethod::class)));
    }

    /**
     * @test
     */
    public function keyIsNameOfMethod(): void
    {
        // cast for phpstan :/
        $methodName = (string) key(methodsOf($this)->data());
        assertTrue(method_exists($this, $methodName));
    }

    /**
     * @test
     * @since  9.2.0
     */
    public function methodsOfNonClassThrowsInvalidArgumentException(): void
    {
        expect(fn() => methodsOf('substr'))
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function propertiesOfReturnsAllMethods(): void
    {
        assertThat(propertiesOf($this)->count(), isGreaterThan(0));
    }

    /**
     * @test
     */
    public function allPropertiesAreInstanceOfReflectionProperty(): void
    {
        assertThat(propertiesOf($this), each(isInstanceOf(ReflectionProperty::class)));
    }

    /**
     * @test
     */
    public function keyIsNameOfProperty(): void
    {
        $propertyName = key(propertiesOf($this)->data());
        assertTrue(isset($this->$propertyName));
    }

    /**
     * @test
     * @since 9.2.0
     */
    public function propertiesOfNonClassThrowsInvalidArgumentException(): void
    {
        expect(fn() => propertiesOf('substr'))
            ->throws(InvalidArgumentException::class);
    }

    public static function argumentsForParametersOf(): Generator
    {
        yield [self::class, 'example'];
        yield [new ReflectionMethod(self::class, 'example')];
    }

    /**
     * @test
     * @dataProvider argumentsForParametersOf
     */
    public function parametersOfReturnsAllParameters(...$reflect): void
    {
        assertThat(parametersOf(...$reflect)->count(), equals(1));
    }

    /**
     * @test
     * @dataProvider argumentsForParametersOf
     */
    public function allParametersOfAreInstanceOfReflectionParameter(...$reflect): void
    {
        assertThat(
            parametersOf(...$reflect),
            each(isInstanceOf(ReflectionParameter::class))
        );
    }

    /**
     * @test
     * @dataProvider  argumentsForParametersOf
     */
    public function keyIsNameOfParameter(...$reflect): void
    {
        assertThat(
            key(parametersOf(...$reflect)->data()),
            equals('refParam')
        );
    }

    /**
     * @test
     * @since 9.2.0
     */
    public function parametersOfWithClassOnlyThrowsInvalidArgumentException(): void
    {
        expect(fn() => parametersOf(__CLASS__))
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     * @since 9.2.0
     */
    public function parametersOfConstructorForClassWithoutConstructorThrowsReflectionException(): void
    {
        expect(fn() => parametersOfConstructor(new ClassWithoutConstructor()))
            ->throws(ReflectionException::class);
    }

    /**
     * @test
     * @since 9.2.0
     */
    public function parametersOfConstructorReturnsListOfParameters(): void
    {
        assertThat(
            parametersOfConstructor(ClassWithConstructor::class)->count(),
            equals(1)
        );
    }

    /**
     * @test
     */
    public function parameterReturnsExactReflectionParameter(): void
    {
        assertThat(
            parameter('refParam', $this, 'example')->getName(),
            equals('refParam')
        );
    }

    /**
     * @test
     * @since 9.2.0
     */
    public function constructorParameterReturnsExactReflectionParameter(): void
    {
        assertThat(
            constructorParameter('example', ClassWithConstructor::class)->getName(),
            equals('example')
        );
    }

    /**
     * @test
     * @since 4.0.0
     */
    public function reflectWithMethodNameReturnsReflectionMethod(): void
    {
        assertThat(
            reflect(__CLASS__, __FUNCTION__),
            isInstanceOf(ReflectionMethod::class)
        );
    }

    /**
     * @test
     * @since 4.0.0
     */
    public function reflectWithClassNameReturnsReflectionClass(): void
    {
        assertThat(reflect(__CLASS__), isInstanceOf(ReflectionClass::class));
    }

    /**
     * @test
     * @since 4.0.0
     */
    public function reflectWithClassInstanceReturnsReflectionObject(): void
    {
        assertThat(reflect($this), isInstanceOf(ReflectionObject::class));
    }

    /**
     * @test
     * @since 4.0.0
     */
    public function reflectWithFunctionNameReturnsReflectionFunction(): void
    {
        assertThat(
            reflect('stubbles\reflect\reflect'),
            isInstanceOf(ReflectionFunction::class)
        );
    }

    /**
     * @test
     * @since 4.0.0
     */
    public function reflectWithUnknownClassAndFunctionNameThrowsReflectionException(): void
    {
        expect(fn() => reflect('doesNotExist'))
            ->throws(ReflectionException::class)
            ->withMessage('Given function or class "doesNotExist" does not exist');
    }

    /**
     * @test
     * @since 4.0.0
     */
    public function reflectInterface(): void
    {
        assertThat(reflect(SomethingToReflect::class), isInstanceOf(ReflectionClass::class));
    }

    /**
     * @test
     * @since 4.1.4
     */
    public function reflectCallableWithInstanceReturnsReflectionMethod(): void
    {
        /** @var callable $callable */
        $callable = [$this, __FUNCTION__];
        assertThat(reflect($callable), isInstanceOf(ReflectionMethod::class));
    }

    /**
     * @test
     * @since 4.1.4
     */
    public function reflectCallableWithClassnameReturnsReflectionMethod(): void
    {
        /** @var callable $callable */
        $callable = [__CLASS__, __FUNCTION__];
        assertThat(reflect($callable), isInstanceOf(ReflectionMethod::class));
    }

    /**
     * @test
     * @since 4.1.4
     */
    public function reflectClosureReturnsReflectionObject(): void
    {
        assertThat(
            reflect(function() { }),
            isInstanceOf(ReflectionObject::class)
        );
    }
}
