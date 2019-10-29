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
     * @type  \stubbles\reflect\annotation\Annotations
     */
    private $annotations;

    protected function setUp(): void
    {
        $this->annotations = new Annotations('someTarget');
    }

    /**
     * @test
     */
    public function doNotContainNonAddedAnnotation()
    {
        assertFalse($this->annotations->contain('foo'));
    }

    /**
     * @test
     */
    public function containsAddedAnnotation()
    {
        assertTrue(
                $this->annotations->add(new Annotation('foo'))->contain('foo')
        );
    }

    /**
     * @test
     */
    public function containsMoreThanOneAnnotation()
    {
        assertTrue(
                $this->annotations->add(new Annotation('foo'))
                        ->add(new Annotation('foo'))
                        ->contain('foo')
        );
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function firstNamedReturnsFirstAddedAnnotationWithThisName()
    {
        $first = new Annotation('foo');
        assertThat(
                $this->annotations->add($first)
                        ->add(new Annotation('foo'))
                        ->firstNamed('foo'),
                isSameAs($first)
        );
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function firstNamedThrowsReflectionExceptionIfNoSuchAnnotationExists()
    {
        expect(function() {
                $this->annotations->firstNamed('foo');
        })
        ->throws(\ReflectionException::class);
    }

    /**
     * @test
     */
    public function returnsEmptyListIfNoneOfThisTypeAdded()
    {
        assertEmptyArray($this->annotations->named('foo'));
    }

    /**
     * @test
     */
    public function returnsAllAnnotationsOfThisType()
    {
        assertThat(
                $this->annotations->add(new Annotation('foo'))
                        ->add(new Annotation('bar'))
                        ->add(new Annotation('foo'))
                        ->named('foo'),
                equals([new Annotation('foo'), new Annotation('foo')])
        );
    }

    /**
     * @test
     */
    public function returnsAllAnnotations()
    {
        assertThat(
                iterator_to_array(
                        $this->annotations->add(new Annotation('foo'))
                                ->add(new Annotation('bar'))
                                ->add(new Annotation('foo'))
                                ->all()
                ),
                equals([
                        new Annotation('foo'),
                        new Annotation('foo'),
                        new Annotation('bar')
                ])
        );
    }

    /**
     * @test
     */
    public function canIteratorOverAllAnnotations()
    {
        $this->annotations->add(new Annotation('foo'))
                ->add(new Annotation('bar'))
                ->add(new Annotation('foo'));
        $types = [];
        foreach ($this->annotations as $annotation) {
            $types[] = $annotation->getAnnotationName();
        }

        assertThat($types, equals(['foo', 'foo', 'bar']));
    }

}
