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

use function bovigo\assert\{
    assertThat,
    assertFalse,
    assertTrue,
    expect,
    fail,
    predicate\equals,
    predicate\hasKey,
    predicate\isOfSize
};
/**
 * Test for stubbles\reflect\annotation\AnnotationCache.
 *
 * @group  reflect
 * @group  annotation
 */
class AnnotationCacheTest extends TestCase
{
    protected function setUp(): void
    {
        AnnotationCache::flush();
        vfsStream::setup();
        AnnotationCache::startFromFileCache(vfsStream::url('root/annotations.cache'));
    }

    protected function tearDown(): void
    {
        AnnotationCache::stop();
    }

    /**
     * @test
     */
    public function noAnnotationAddedDoesNotWriteCacheFile(): void
    {
        AnnotationCache::__shutdown();
        assertFalse(file_exists(vfsStream::url('root/annotations.cache')));
    }

    /**
     * @test
     */
    public function addingAnnotationWritesCacheFile(): void
    {
        $annotations = new Annotations('someTarget');
        AnnotationCache::put($annotations);
        AnnotationCache::__shutdown();
        assertTrue(file_exists(vfsStream::url('root/annotations.cache')));
    }

    /**
     * @test
     */
    public function cacheFileContainsSerializedAnnotationData(): void
    {
        $annotations = new Annotations('someTarget');
        AnnotationCache::put($annotations);
        AnnotationCache::__shutdown();
        $fileContent = file_get_contents(vfsStream::url('root/annotations.cache'));
        if (false === $fileContent) {
            fail('Could not read annotations cache');
        }

        $data = unserialize($fileContent);
        assertThat($data, hasKey('someTarget'));
        assertThat(unserialize($data['someTarget']), equals($annotations));
    }

    /**
     * @since  3.0.0
     * @group  issue_58
     * @test
     */
    public function stoppingAnnotationPersistenceDoesNotWriteCacheFileOnShutdown(): void
    {
        AnnotationCache::put(new Annotations('someTarget'));
        AnnotationCache::stop();
        AnnotationCache::__shutdown();
        assertFalse(file_exists(vfsStream::url('root/annotations.cache')));
    }

    /**
     * @test
     */
    public function retrieveAnnotationsForUncachedTargetReturnsEmptyAnnotations(): void
    {
        $annotations = iterator_to_array(AnnotationCache::get('DoesNotExist'));
        assertThat($annotations, isOfSize(0));
    }

    /**
     * @since  3.0.0
     * @group  issue_58
     * @test
     */
    public function startAnnotationCacheWithInvalidCacheDataThrowsRuntimeException(): void
    {
        expect(function() {
            AnnotationCache::start(
                function() { return serialize('foo'); },
                function(): void {}
            );
        })
        ->throws(\RuntimeException::class);
    }

    /**
     * @since  3.0.0
     * @group  issue_58
     * @test
     */
    public function startAnnotationCacheWithNonSerializedCacheDataThrowsRuntimeException(): void
    {
        expect(function() {
            AnnotationCache::start(function() { return 'foo'; }, function(): void {});
        })
        ->throws(\RuntimeException::class);
    }

    /**
     * @test
     * @since  9.2.0
     */
    public function annotationDataFromCacheCanBeRetrieved(): void
    {
        $a  = new Annotations(__CLASS__);
        AnnotationCache::start(
            function() use ($a) { return [__CLASS__ => serialize($a)]; },
            function(): void {}
        );
        assertThat(AnnotationCache::get(__CLASS__), equals($a));
    }
}
