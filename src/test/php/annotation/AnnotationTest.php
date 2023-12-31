<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\{
    assertThat,
    assertFalse,
    assertNull,
    assertTrue,
    expect,
    predicate\equals
};
/**
 * Test for stubbles\reflect\annotation\Annotation.
 */
#[Group('reflect')]
#[Group('annotation')]
#[Group('bug252')]
class AnnotationTest extends TestCase
{
    const TEST_CONSTANT = 'baz';

    /**
     * @param   array<string,mixed> $values
     * @return  \stubbles\reflect\annotation\Annotation
     */
    private function createAnnotation(array $values = []): Annotation
    {
        return new Annotation('Life', 'someFunction()', $values, 'Example');
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function returnsGivenTargetName(): void
    {
        assertThat($this->createAnnotation()->target(), equals('someFunction()'));
    }

    #[Test]
    public function callUndefinedMethodThrowsReflectionException(): void
    {
        expect(function() { $this->createAnnotation()->invalid(); })
            ->throws(\ReflectionException::class)
            ->withMessage('The value with name "invalid" for annotation @Example[Life] at someFunction() does not exist');
    }

    private function createSingleValueAnnotation(string $value): Annotation
    {
        return $this->createAnnotation(['__value' => $value]);
    }

    #[Test]
    public function returnsSpecialValueForAllMethodCallsWithGet(): void
    {
        $annotation = $this->createSingleValueAnnotation('bar');
        assertThat($annotation->getFoo(), equals('bar'));
        assertThat($annotation->getOther(), equals('bar'));
    }

    #[Test]
    public function returnsSpecialValueForAllMethodCallsWithIs(): void
    {
        $annotation = $this->createSingleValueAnnotation('true');
        assertTrue($annotation->isFoo());
        assertTrue($annotation->isOther());
    }

    #[Test]
    public function throwsReflectionExceptionForMethodCallsWithoutGetOrIsOnSpecialValue(): void
    {
        expect(function() {
            $annotation = new Annotation('Example', 'someFunction()', ['__value' => 'true']);
            $annotation->invalid();
        })
            ->throws(\ReflectionException::class)
            ->withMessage('The value with name "invalid" for annotation @Example at someFunction() does not exist');
    }

    /**
     * @since  1.7.0
     */
    #[Test]
    #[Group('value_by_name')]
    public function returnsFalseOnCheckForUnsetProperty(): void
    {
        assertFalse($this->createAnnotation()->hasValueByName('foo'));
    }

    /**
     * @since  1.7.0
     */
    #[Test]
    #[Group('value_by_name')]
    public function returnsTrueOnCheckForSetProperty(): void
    {
        assertTrue(
                $this->createAnnotation(['foo' => 'hello'])
                        ->hasValueByName('foo')
        );
    }

    /**
     * @since  1.7.0
     */
    #[Test]
    #[Group('value_by_name')]
    public function returnsNullForUnsetProperty(): void
    {
        assertNull($this->createAnnotation()->getValueByName('foo'));
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    #[Group('value_by_name')]
    public function returnsDefaultForUnsetProperty(): void
    {
        assertThat(
                $this->createAnnotation()->getValueByName('foo', 'bar'),
                equals('bar')
        );
    }

    /**
     * @since  1.7.0
     */
    #[Test]
    #[Group('value_by_name')]
    public function returnsValueForSetProperty(): void
    {
        assertThat(
                $this->createAnnotation(['foo' => 'hello'])->getValueByName('foo'),
                equals('hello')
        );
    }

    #[Test]
    public function returnsNullForUnsetGetProperty(): void
    {
        assertNull($this->createAnnotation()->getFoo());
    }

    #[Test]
    public function returnsFalseForUnsetBooleanProperty(): void
    {
        assertFalse($this->createAnnotation()->isFoo());
    }

    #[Test]
    public function returnsValueOfGetProperty(): void
    {
        assertThat(
                $this->createAnnotation(['foo' => 'bar'])->getFoo(),
                equals('bar')
        );
    }

    #[Test]
    public function returnsFirstArgumentIfGetPropertyNotSet(): void
    {
        assertThat(
                $this->createAnnotation()->getFoo('bar'),
                equals('bar')
        );
    }

    /**
     * @return  array<string[]>
     */
    public static function booleanValues(): array
    {
        return [['true'], ['yes'], ['on']];
    }

    #[Test]
    #[DataProvider('booleanValues')]
    public function returnsValueOfBooleanProperty(string $bool): void
    {
        assertTrue($this->createAnnotation(['foo' => $bool])->isFoo());
    }

    #[Test]
    public function returnTrueForValueCheckIfValueSet(): void
    {
        assertTrue($this->createSingleValueAnnotation('bar')->hasValue());
    }

    #[Test]
    public function returnFalseForValueCheckIfValueNotSet(): void
    {
        assertFalse($this->createAnnotation()->hasValue());
    }

    #[Test]
    public function returnFalseForValueCheckIfAnotherPropertySet(): void
    {
        assertFalse($this->createAnnotation(['foo' => 'bar'])->hasValue());
    }

    #[Test]
    public function returnTrueForPropertyCheckIfPropertySet(): void
    {
        $annotation = $this->createAnnotation(['foo' => 'bar']);
        assertTrue($annotation->hasFoo());
    }

    #[Test]
    public function returnFalseForPropertyCheckIfPropertyNotSet(): void
    {
        assertFalse($this->createAnnotation()->hasFoo());
    }

    #[Test]
    public function canAccessPropertyAsMethod(): void
    {
        assertThat(
                $this->createAnnotation(['foo' => 'bar'])->foo(),
                equals('bar')
        );
    }

    #[Test]
    public function canAccessBooleanPropertyAsMethod(): void
    {
        assertTrue($this->createAnnotation(['foo' => 'true'])->foo());
    }

    /**
     * @return  array<array<mixed>>
     */
    public static function valueTypes(): array
    {
        return [
            [true, 'true'],
            [false, 'false'],
            [null, 'null'],
            [4562, '4562'],
            [-13, '-13'],
            [2.34, '2.34'],
            [-5.67, '-5.67'],
            [new \ReflectionClass(__CLASS__), __CLASS__ . '.class'],
            ['true', "'true'"],
            ['null', '"null"'],
            [AnnotationTest::TEST_CONSTANT, __CLASS__ . '::TEST_CONSTANT']
        ];
    }

    /**
     * @since  4.1.0
     */
    #[Test]
    #[DataProvider('valueTypes')]
    public function parsesValuesToTypes(mixed $expected, string $stringValue): void
    {
        assertThat(
                $this->createAnnotation(['foo' => $stringValue])->foo(),
                equals($expected)
        );
    }

    /**
     * @since  4.1.0
     */
    #[Test]
    #[DataProvider('valueTypes')]
    public function parsesValuesToTypesWithGet(mixed $expected, string $stringValue): void
    {
        assertThat(
                $this->createAnnotation(['foo' => $stringValue])->getFoo(),
                equals($expected)
        );
    }

    /**
     * @since  4.1.0
     */
    #[Test]
    #[DataProvider('valueTypes')]
    public function parsesValuesToTypesWithGetValueByName(mixed $expected, string $stringValue): void
    {
        assertThat(
                $this->createAnnotation(['foo' => $stringValue])->getValueByName('foo'),
                equals($expected)
        );
    }

    /**
     * @since  4.1.0
     */
    #[Test]
    #[DataProvider('valueTypes')]
    public function parsesValuesToTypesWithSingleValue(mixed $expected, string $stringValue): void
    {
        assertThat(
                $this->createSingleValueAnnotation($stringValue)->getValue(),
                equals($expected)
        );
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    public function canBeCastedToString(): void
    {
        assertThat(
                (string) $this->createAnnotation(['foo' => 303, 'bar' => "'value'"]),
                equals("@Life[Example](foo=303, bar='value')")
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function canBeCastedToStringWithSingleValue(): void
    {
        assertThat(
                (string) $this->createAnnotation(['__value' => 303]),
                equals("@Life[Example](303)")
        );
    }

    /**
     * @since  5.0.0
     * @return  array<array<mixed>>
     */
    public static function parseList(): array
    {
        return [
            ['This is a string', 'This is a string', 'asString'],
            [303, '303', 'asInt'],
            [3.13, '3.13', 'asFloat'],
            [false, '1', 'asBool'],
            [true, 'true', 'asBool'],
            [false, 'false', 'asBool'],
            [['foo', 'bar', 'baz'], '[foo|bar|baz]', 'asList'],
            [['foo' => 'bar', 'baz'], 'foo:bar|baz', 'asMap'],
            [[1, 2, 3, 4, 5], '1..5', 'asRange']
        ];
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    #[DataProvider('parseList')]
    public function parseReturnsValueCastedToRecognizedType(mixed $expected, string $value, string $type): void
    {
        assertThat(
                $this->createAnnotation(['foo' => $value])->parse('foo')->$type(),
                equals($expected)
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function parseReturnsNullWhenNoValueForRequestedNameExists(): void
    {
        assertNull(
                $this->createAnnotation(['foo' => 303])->parse('bar')->asInt()
        );
    }
}
