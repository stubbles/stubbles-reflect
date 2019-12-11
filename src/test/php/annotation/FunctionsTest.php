<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\lang\*().
 *
 * @since  3.1.0
 * @group  lang
 * @group  lang_core
 */
class FunctionsTest extends TestCase
{
    protected function tearDown(): void
    {
        AnnotationCache::stop();
    }

    /**
     * @since  3.0.0
     * @group  issue_58
     * @test
     */
    public function canEnableFileAnnotationCache(): void
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('annotations.cache')
                         ->withContent(serialize($this->createdCachedAnnotation()))
                         ->at($root);
        persistAnnotationsInFile($file->url());
        assertTrue(AnnotationCache::has('foo'));
    }

    /**
     * @return  array<string,array<string,Annotation>>
     */
    private function createdCachedAnnotation(): array
    {
        return ['foo' => ['bar' => new Annotation('bar', 'someFunction()')]];
    }

    /**
     * @since  3.1.0
     * @group  issue_58
     * @test
     */
    public function canEnableOtherAnnotationCache(): void
    {
        $annotationData = $this->createdCachedAnnotation();
        persistAnnotations(function() use($annotationData)
                           {
                               return $annotationData;
                           },
                           function($data) {}
        );
        assertTrue(AnnotationCache::has('foo'));
    }
}
