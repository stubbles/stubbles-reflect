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
    assertEmptyArray,
    assertFalse,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isSameAs
};
/**
 * Test for stubbles\reflect\annotation\Annotations.
 *
 * @group  reflect
 * @group  annotation
 * @since  5.0.0
 */
class AnnotationsTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  \stubbles\reflect\annotation\Annotations
     */
    private $annotations;

    protected function setUp(): void
    {
        $this->annotations = new Annotations('someTarget');
    }

    /**
     * @test
     */
    public function doNotContainNonAddedAnnotation(): void
    {
        assertFalse($this->annotations->contain('foo'));
    }

    /**
     * @test
     */
    public function containsAddedAnnotation(): void
    {
        assertTrue(
                $this->annotations->add(new Annotation('foo', __METHOD__))->contain('foo')
        );
    }

    /**
     * @test
     */
    public function containsMoreThanOneAnnotation(): void
    {
        assertTrue(
                $this->annotations->add(new Annotation('foo', __METHOD__))
                        ->add(new Annotation('foo', __METHOD__))
                        ->contain('foo')
        );
    }

    /**
     * @test
     * @since  5.3.0
     */
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
     * @test
     * @since  5.3.0
     */
    public function firstNamedThrowsReflectionExceptionIfNoSuchAnnotationExists(): void
    {
        expect(function() { $this->annotations->firstNamed('foo'); })
            ->throws(\ReflectionException::class);
    }

    /**
     * @test
     */
    public function returnsEmptyListIfNoneOfThisTypeAdded(): void
    {
        assertEmptyArray($this->annotations->named('foo'));
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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
