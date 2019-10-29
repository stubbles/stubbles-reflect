<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation;
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
 *
 * @group  reflect
 * @group  annotation
 * @group  bug252
 */
class AnnotationTest extends TestCase
{
    const TEST_CONSTANT = 'baz';

    /**
     * @param   array $values
     * @return  \stubbles\reflect\annotation\Annotation
     */
    private function createAnnotation(array $values = []): Annotation
    {
        return new Annotation('Life', 'someFunction()', $values, 'Example');
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function returnsGivenTargetName()
    {
        assertThat($this->createAnnotation()->target(), equals('someFunction()'));
    }

    /**
     * @test
     */
    public function callUndefinedMethodThrowsReflectionException()
    {
        expect(function() {
                $this->createAnnotation()->invalid();
        })
        ->throws(\ReflectionException::class)
        ->withMessage('The value with name "invalid" for annotation @Example[Life] at someFunction() does not exist');
    }

    /**
     * @param   string  $value
     * @return  \stubbles\reflect\annotation\Annotation
     */
    private function createSingleValueAnnotation($value)
    {
        return $this->createAnnotation(['__value' => $value]);
    }

    /**
     * @test
     */
    public function returnsSpecialValueForAllMethodCallsWithGet()
    {
        $annotation = $this->createSingleValueAnnotation('bar');
        assertThat($annotation->getFoo(), equals('bar'));
        assertThat($annotation->getOther(), equals('bar'));
    }

    /**
     * @test
     */
    public function returnsSpecialValueForAllMethodCallsWithIs()
    {
        $annotation = $this->createSingleValueAnnotation('true');
        assertTrue($annotation->isFoo());
        assertTrue($annotation->isOther());
    }

    /**
     * @test
     */
    public function throwsReflectionExceptionForMethodCallsWithoutGetOrIsOnSpecialValue()
    {
        expect(function() {
                $annotation = new Annotation('Example', 'someFunction()', ['__value' => 'true']);
                $annotation->invalid();
        })
        ->throws(\ReflectionException::class)
        ->withMessage('The value with name "invalid" for annotation @Example at someFunction() does not exist');
    }

    /**
     * @test
     * @group  value_by_name
     * @since  1.7.0
     */
    public function returnsFalseOnCheckForUnsetProperty()
    {
        assertFalse($this->createAnnotation()->hasValueByName('foo'));
    }

    /**
     * @test
     * @group  value_by_name
     * @since  1.7.0
     */
    public function returnsTrueOnCheckForSetProperty()
    {
        assertTrue(
                $this->createAnnotation(['foo' => 'hello'])
                        ->hasValueByName('foo')
        );
    }

    /**
     * @test
     * @group  value_by_name
     * @since  1.7.0
     */
    public function returnsNullForUnsetProperty()
    {
        assertNull($this->createAnnotation()->getValueByName('foo'));
    }

    /**
     * @test
     * @group  value_by_name
     * @since  5.0.0
     */
    public function returnsDefaultForUnsetProperty()
    {
        assertThat(
                $this->createAnnotation()->getValueByName('foo', 'bar'),
                equals('bar')
        );
    }

    /**
     * @test
     * @group  value_by_name
     * @since  1.7.0
     */
    public function returnsValueForSetProperty()
    {
        assertThat(
                $this->createAnnotation(['foo' => 'hello'])->getValueByName('foo'),
                equals('hello')
        );
    }

    /**
     * @test
     */
    public function returnsNullForUnsetGetProperty()
    {
        assertNull($this->createAnnotation()->getFoo());
    }

    /**
     * @test
     */
    public function returnsFalseForUnsetBooleanProperty()
    {
        assertFalse($this->createAnnotation()->isFoo());
    }

    /**
     * @test
     */
    public function returnsValueOfGetProperty()
    {
        assertThat(
                $this->createAnnotation(['foo' => 'bar'])->getFoo(),
                equals('bar')
        );
    }

    /**
     * @test
     */
    public function returnsFirstArgumentIfGetPropertyNotSet()
    {
        assertThat(
                $this->createAnnotation()->getFoo('bar'),
                equals('bar')
        );
    }

    public function booleanValues(): array
    {
        return [['true'], ['yes'], ['on']];
    }

    /**
     * @test
     * @dataProvider  booleanValues
     */
    public function returnsValueOfBooleanProperty($bool)
    {
        assertTrue($this->createAnnotation(['foo' => $bool])->isFoo());
    }

    /**
     * @test
     */
    public function returnTrueForValueCheckIfValueSet()
    {
        assertTrue($this->createSingleValueAnnotation('bar')->hasValue());
    }

    /**
     * @test
     */
    public function returnFalseForValueCheckIfValueNotSet()
    {
        assertFalse($this->createAnnotation()->hasValue());
    }

    /**
     * @test
     */
    public function returnFalseForValueCheckIfAnotherPropertySet()
    {
        assertFalse($this->createAnnotation(['foo' => 'bar'])->hasValue());
    }

    /**
     * @test
     */
    public function returnTrueForPropertyCheckIfPropertySet()
    {
        $annotation = $this->createAnnotation(['foo' => 'bar']);
        assertTrue($annotation->hasFoo());
    }

    /**
     * @test
     */
    public function returnFalseForPropertyCheckIfPropertyNotSet()
    {
        assertFalse($this->createAnnotation()->hasFoo());
    }

    /**
     * @test
     */
    public function canAccessPropertyAsMethod()
    {
        assertThat(
                $this->createAnnotation(['foo' => 'bar'])->foo(),
                equals('bar')
        );
    }

    /**
     * @test
     */
    public function canAccessBooleanPropertyAsMethod()
    {
        assertTrue($this->createAnnotation(['foo' => 'true'])->foo());
    }

    public function valueTypes(): array
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
     * @test
     * @dataProvider  valueTypes
     * @since  4.1.0
     */
    public function parsesValuesToTypes($expected, string $stringValue)
    {
        assertThat(
                $this->createAnnotation(['foo' => $stringValue])->foo(),
                equals($expected)
        );
    }

    /**
     * @test
     * @dataProvider  valueTypes
     * @since  4.1.0
     */
    public function parsesValuesToTypesWithGet($expected, string $stringValue)
    {
        assertThat(
                $this->createAnnotation(['foo' => $stringValue])->getFoo(),
                equals($expected)
        );
    }

    /**
     * @test
     * @dataProvider  valueTypes
     * @since  4.1.0
     */
    public function parsesValuesToTypesWithGetValueByName($expected, string $stringValue)
    {
        assertThat(
                $this->createAnnotation(['foo' => $stringValue])->getValueByName('foo'),
                equals($expected)
        );
    }

    /**
     * @test
     * @dataProvider  valueTypes
     * @since  4.1.0
     */
    public function parsesValuesToTypesWithSingleValue($expected, string $stringValue)
    {
        assertThat(
                $this->createSingleValueAnnotation($stringValue)->getValue(),
                equals($expected)
        );
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function canBeCastedToString()
    {
        assertThat(
                (string) $this->createAnnotation(['foo' => 303, 'bar' => "'value'"]),
                equals("@Life[Example](foo=303, bar='value')")
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function canBeCastedToStringWithSingleValue()
    {
        assertThat(
                (string) $this->createAnnotation(['__value' => 303]),
                equals("@Life[Example](303)")
        );
    }

    /**
     * @since  5.0.0
     */
    public function parseList(): array
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
     * @param  mixed   $expected
     * @param  string  $value
     * @param  string  $type
     * @test
     * @dataProvider  parseList
     * @since  5.0.0
     */
    public function parseReturnsValueCastedToRecognizedType($expected, $value, $type)
    {
        assertThat(
                $this->createAnnotation(['foo' => $value])->parse('foo')->$type(),
                equals($expected)
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function parseReturnsNullWhenNoValueForRequestedNameExists()
    {
        assertNull(
                $this->createAnnotation(['foo' => 303])->parse('bar')->asInt()
        );
    }
}
