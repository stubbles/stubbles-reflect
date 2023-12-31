<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\{
    assertThat,
    assertEmptyArray,
    assertFalse,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isEmpty,
    predicate\isSameAs
};
/**
 * Test for stubbles\reflect\annotation\Annotations.
 *
 * @since  5.0.0
 */
#[Group('reflect')]
#[Group('annotation')]
class AnnotationsTest extends TestCase
{
    private Annotations $annotations;

    protected function setUp(): void
    {
        $this->annotations = new Annotations('someTarget');
    }

    /**
     * @since  9.2.0
     */
    #[Test]
    public function annotationCountIs0ByDefault(): void
    {
        assertThat($this->annotations, isEmpty());
    }

    #[Test]
    public function doNotContainNonAddedAnnotation(): void
    {
        assertFalse($this->annotations->contain('foo'));
    }

    #[Test]
    public function containsAddedAnnotation(): void
    {
        assertTrue(
                $this->annotations->add(new Annotation('foo', __METHOD__))->contain('foo')
        );
    }

    /**
     * @since  9.2.0
     */
    #[Test]
    public function containsAddedAnnotationRaisesCountTo1(): void
    {
        assertThat($this->annotations->add(new Annotation('foo', __METHOD__))->count(), equals(1));
    }

    #[Test]
    public function containsMoreThanOneAnnotation(): void
    {
        assertTrue(
                $this->annotations->add(new Annotation('foo', __METHOD__))
                        ->add(new Annotation('foo', __METHOD__))
                        ->contain('foo')
        );
    }

    #[Test]
    public function containsMoreThanOneAnnotationRaisesCount(): void
    {
        assertThat(
            $this->annotations->add(new Annotation('foo', __METHOD__))
                 ->add(new Annotation('foo', __METHOD__))
                 ->count(),
            equals(2)
        );
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function firstNamedReturnsFirstAddedAnnotationWithThisName(): void
    {
        $first = new Annotation('foo', __METHOD__);
        assertThat(
                $this->annotations->add($first)
                        ->add(new Annotation('foo', __METHOD__))
                        ->firstNamed('foo'),
                isSameAs($first)
        );
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function firstNamedThrowsReflectionExceptionIfNoSuchAnnotationExists(): void
    {
        expect(function() { $this->annotations->firstNamed('foo'); })
            ->throws(\ReflectionException::class);
    }

    #[Test]
    public function returnsEmptyListIfNoneOfThisTypeAdded(): void
    {
        assertEmptyArray($this->annotations->named('foo'));
    }

    #[Test]
    public function returnsAllAnnotationsOfThisType(): void
    {
        assertThat(
                $this->annotations->add(new Annotation('foo', __METHOD__))
                        ->add(new Annotation('bar', __METHOD__))
                        ->add(new Annotation('foo', __METHOD__))
                        ->named('foo'),
                equals([new Annotation('foo', __METHOD__), new Annotation('foo', __METHOD__)])
        );
    }

    #[Test]
    public function returnsAllAnnotations(): void
    {
        assertThat(
                iterator_to_array(
                        $this->annotations->add(new Annotation('foo', __METHOD__))
                                ->add(new Annotation('bar', __METHOD__))
                                ->add(new Annotation('foo', __METHOD__))
                                ->all()
                ),
                equals([
                        new Annotation('foo', __METHOD__),
                        new Annotation('foo', __METHOD__),
                        new Annotation('bar', __METHOD__)
                ])
        );
    }

    #[Test]
    public function canIteratorOverAllAnnotations(): void
    {
        $this->annotations->add(new Annotation('foo', __METHOD__))
                ->add(new Annotation('bar', __METHOD__))
                ->add(new Annotation('foo', __METHOD__));
        $types = [];
        foreach ($this->annotations as $annotation) {
            $types[] = $annotation->getAnnotationName();
        }

        assertThat($types, equals(['foo', 'foo', 'bar']));
    }

}
