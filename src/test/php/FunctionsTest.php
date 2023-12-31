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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
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
 */
#[Group('reflect')]
#[Group('functions')]
class FunctionsTest extends TestCase
{
    #[Test]
    public function annotationsWithMethodNameReturnsMethodAnnotations(): void
    {
        assertThat(
            annotationsOf(__CLASS__, __FUNCTION__)->target(),
            equals(__CLASS__ . '::' . __FUNCTION__ . '()')
        );
    }

    #[Test]
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
     * @param class-string|object|ReflectionClass $toReflect
     */
    #[Test]
    #[DataProvider('argumentVariantsOfClassWithoutConstructor')]
    public function constructorAnnotationsForClassWithoutConstructorThrowsReflectionException(
        string|object $toReflect
    ): void {
        expect(fn() => annotationsOfConstructor($toReflect))
            ->throws(\ReflectionException::class)
            ->withMessage('Method stubbles\reflect\ClassWithoutConstructor::__construct() does not exist');
    }

    #[Test]
    public function constructorAnnotationsWithClassNameReturnsConstructorAnnotations(): void
    {
        assertThat(
            annotationsOfConstructor(__CLASS__)->target(),
            equals(TestCase::class . '::__construct()')
        );
    }

    #[Test]
    public function annotationsWithClassInstanceReturnsClassAnnotations(): void
    {
        assertThat(annotationsOf($this)->target(), equals(__CLASS__));
    }

    #[Test]
    public function constructorAnnotationsWithClassInstanceReturnsConstructorAnnotations(): void
    {
        assertThat(
                annotationsOfConstructor($this)->target(),
                equals(TestCase::class . '::__construct()')
        );
    }

    #[Test]
    public function annotationsWithFunctionNameReturnsFunctionAnnotations(): void
    {
        assertThat(
                annotationsOf('stubbles\reflect\annotationsOf')->target(),
                equals('stubbles\reflect\annotationsOf()')
        );
    }

    #[Test]
    public function annotationsWithUnknownClassAndFunctionNameThrowsReflectionException(): void
    {
        expect(function() { annotationsOf('doesNotExist'); })
            ->throws(\ReflectionException::class)
            ->withMessage('Given function or class "doesNotExist" does not exist');
    }

    private function example(string $refParam): void
    {
        // intentionally empty
    }

    #[Test]
    public function annotationsWithReflectionParameterReturnsParameterAnnotations(): void
    {
        $refParam = new ReflectionParameter([$this, 'example'], 'refParam');
        assertThat(
            annotationsOf($refParam)->target(),
            equals(__CLASS__ . '::example()#refParam')
        );
    }

    #[Test]
    public function annotationsOfParameterWithClassInstanceReturnsParameterAnnotations(): void
    {
        assertThat(
            annotationsOfParameter('refParam', $this, 'example')->target(),
            equals(__CLASS__ . '::example()#refParam')
        );
    }

    #[Test]
    public function annotationsOfParameterWithClassNameReturnsParameterAnnotations(): void
    {
        assertThat(
            annotationsOfParameter('refParam', __CLASS__, 'example')->target(),
            equals(__CLASS__ . '::example()#refParam')
        );
    }

    /**
     * @since 9.2.0
     */
    #[Test]
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

    #[Test]
    #[DataProvider('properties')]
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

    #[Test]
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
     * @since 8.0.1
     */
    #[Test]
    #[DataProvider('reflectorWithUndefinedDocComments')]
    public function undefinedDocCommentIsEmpty(Reflector $reflector): void
    {
        assertEmptyString(docComment($reflector));
    }

    #[Test]
    public function docCommentThrowsReflectionExceptionForNonSupportedAnnotationPlaces(): void
    {
        expect(fn() => docComment(new ReflectionExtension('date')))
            ->throws(ReflectionException::class);
    }

    #[Test]
    public function methodsOfReturnsAllMethods(): void
    {
        assertThat(methodsOf($this)->count(), isGreaterThan(0));
    }

    #[Test]
    public function allMethodsAreInstanceOfReflectionMethod(): void
    {
        assertThat(methodsOf($this), each(isInstanceOf(ReflectionMethod::class)));
    }

    #[Test]
    public function keyIsNameOfMethod(): void
    {
        // cast for phpstan :/
        $methodName = (string) key(methodsOf($this)->data());
        assertTrue(method_exists($this, $methodName));
    }

    /**
     * @since  9.2.0
     */
    #[Test]
    public function methodsOfNonClassThrowsInvalidArgumentException(): void
    {
        expect(fn() => methodsOf('substr'))
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function propertiesOfReturnsAllMethods(): void
    {
        assertThat(propertiesOf($this)->count(), isGreaterThan(0));
    }

    #[Test]
    public function allPropertiesAreInstanceOfReflectionProperty(): void
    {
        assertThat(propertiesOf($this), each(isInstanceOf(ReflectionProperty::class)));
    }

    #[Test]
    public function keyIsNameOfProperty(): void
    {
        $propertyName = key(propertiesOf($this)->data());
        assertTrue(isset($this->$propertyName));
    }

    /**
     * @since 9.2.0
     */
    #[Test]
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

    #[Test]
    #[DataProvider('argumentsForParametersOf')]
    public function parametersOfReturnsAllParameters(...$reflect): void
    {
        assertThat(parametersOf(...$reflect)->count(), equals(1));
    }

    #[Test]
    #[DataProvider('argumentsForParametersOf')]
    public function allParametersOfAreInstanceOfReflectionParameter(...$reflect): void
    {
        assertThat(
            parametersOf(...$reflect),
            each(isInstanceOf(ReflectionParameter::class))
        );
    }

    #[Test]
    #[DataProvider('argumentsForParametersOf')]
    public function keyIsNameOfParameter(...$reflect): void
    {
        assertThat(
            key(parametersOf(...$reflect)->data()),
            equals('refParam')
        );
    }

    /**
     * @since 9.2.0
     */
    #[Test]
    public function parametersOfWithClassOnlyThrowsInvalidArgumentException(): void
    {
        expect(fn() => parametersOf(__CLASS__))
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @since 9.2.0
     */
    #[Test]
    public function parametersOfConstructorForClassWithoutConstructorThrowsReflectionException(): void
    {
        expect(fn() => parametersOfConstructor(new ClassWithoutConstructor()))
            ->throws(ReflectionException::class);
    }

    /**
     * @since 9.2.0
     */
    #[Test]
    public function parametersOfConstructorReturnsListOfParameters(): void
    {
        assertThat(
            parametersOfConstructor(ClassWithConstructor::class)->count(),
            equals(1)
        );
    }

    #[Test]
    public function parameterReturnsExactReflectionParameter(): void
    {
        assertThat(
            parameter('refParam', $this, 'example')->getName(),
            equals('refParam')
        );
    }

    /**
     * @since 9.2.0
     */
    #[Test]
    public function constructorParameterReturnsExactReflectionParameter(): void
    {
        assertThat(
            constructorParameter('example', ClassWithConstructor::class)->getName(),
            equals('example')
        );
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function reflectWithMethodNameReturnsReflectionMethod(): void
    {
        assertThat(
            reflect(__CLASS__, __FUNCTION__),
            isInstanceOf(ReflectionMethod::class)
        );
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function reflectWithClassNameReturnsReflectionClass(): void
    {
        assertThat(reflect(__CLASS__), isInstanceOf(ReflectionClass::class));
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function reflectWithClassInstanceReturnsReflectionObject(): void
    {
        assertThat(reflect($this), isInstanceOf(ReflectionObject::class));
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function reflectWithFunctionNameReturnsReflectionFunction(): void
    {
        assertThat(
            reflect('stubbles\reflect\reflect'),
            isInstanceOf(ReflectionFunction::class)
        );
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function reflectWithUnknownClassAndFunctionNameThrowsReflectionException(): void
    {
        expect(fn() => reflect('doesNotExist'))
            ->throws(ReflectionException::class)
            ->withMessage('Given function or class "doesNotExist" does not exist');
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function reflectInterface(): void
    {
        assertThat(reflect(SomethingToReflect::class), isInstanceOf(ReflectionClass::class));
    }

    /**
     * @since 4.1.4
     */
    #[Test]
    public function reflectCallableWithInstanceReturnsReflectionMethod(): void
    {
        /** @var callable $callable */
        $callable = [$this, __FUNCTION__];
        assertThat(reflect($callable), isInstanceOf(ReflectionMethod::class));
    }

    /**
     * @since 4.1.4
     */
    #[Test]
    public function reflectCallableWithClassnameReturnsReflectionMethod(): void
    {
        /** @var callable $callable */
        $callable = [__CLASS__, __FUNCTION__];
        assertThat(reflect($callable), isInstanceOf(ReflectionMethod::class));
    }

    /**
     * @since 4.1.4
     */
    #[Test]
    public function reflectClosureReturnsReflectionObject(): void
    {
        assertThat(
            reflect(function() { }),
            isInstanceOf(ReflectionObject::class)
        );
    }
}
