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
namespace stubbles\reflect\annotation;
use function bovigo\assert\{
    assert,
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
class AnnotationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\reflect\annotation\Annotations
     */
    private $annotations;

    /**
     * set up test environment
     */
    public function setUp()
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
        assert(
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
        assert(
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
        assert(
                $this->annotations->add(new Annotation('foo'))
                        ->add(new Annotation('bar'))
                        ->add(new Annotation('foo'))
                        ->all(),
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

        assert($types, equals(['foo', 'foo', 'bar']));
    }

}
