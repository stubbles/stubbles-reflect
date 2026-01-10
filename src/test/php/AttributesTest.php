<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stubbles\reflect\test\helper\ClassWithConstructor;
use stubbles\reflect\test\helper\ClassWithoutConstructor;
use stubbles\reflect\test\helper\SomeClassAttribute;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\each;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isEmpty;
use function bovigo\assert\predicate\isFalse;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isTrue;

/**
 * @since 11.1.0
 */
#[Group('attributes')]
class AttributesTest extends TestCase
{
    #[Test]
    public function attributesAreEmptyWhenNoneSet(): void
    {
        assertThat(attributesOf(ClassWithoutConstructor::class)->isEmpty(), isTrue());
    }

    #[Test]
    public function attributesAreNotEmptyWhenOnesSet(): void
    {
        assertThat(attributesOf(ClassWithConstructor::class)->isEmpty(), isFalse());
    }

    #[Test]
    public function attributeIsNotContainedWhenNotSet(): void
    {
        assertThat(attributesOf(ClassWithConstructor::class)->contain(AttributesTest::class), isFalse());
    }

    #[Test]
    public function attributeIsContainedWhenSet(): void
    {
        assertThat(attributesOf(ClassWithConstructor::class)->contain(SomeClassAttribute::class), isTrue());
    }

    #[Test]
    public function firstNamedThrowsExceptionWhenGivenAttributeNotSet(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Can not find attribute #['. __CLASS__ . '] for ' . ClassWithConstructor::class);

        attributesOf(ClassWithConstructor::class)->firstNamed(AttributesTest::class);
    }

    #[Test]
    public function firstNamedReturnsFirstInstanceSetOnReflected(): void
    {
        $someClassAttribute = attributesOf(ClassWithConstructor::class)->firstNamed(SomeClassAttribute::class);
        assertThat(
            $someClassAttribute,
            isInstanceOf(SomeClassAttribute::class)
        );
        assertThat($someClassAttribute->value, equals(303));
    }

    #[Test]
    public function namedReturnsEmptyListWhenNoSuchAttributeSet(): void
    {
        assertThat(
            attributesOf(ClassWithConstructor::class)->named(AttributesTest::class),
            isEmpty()
        );
    }

    #[Test]
    public function namedReturnsListOfAllAttributesOfGivenType(): void
    {
        $someClassAttributes = attributesOf(ClassWithConstructor::class)->named(SomeClassAttribute::class);

        assertThat(count($someClassAttributes), equals(2));
        assertThat($someClassAttributes, each(isInstanceOf(SomeClassAttribute::class)));
        assertThat($someClassAttributes[0]->value, equals(303));
        assertThat($someClassAttributes[1]->value, equals(404));
    }
}
