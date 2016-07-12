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
use org\bovigo\vfs\vfsStream;

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\hasKey;
/**
 * Test for stubbles\reflect\annotation\AnnotationCache.
 *
 * @group  reflect
 * @group  annotation
 */
class AnnotationCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        AnnotationCache::flush();
        vfsStream::setup();
        AnnotationCache::startFromFileCache(vfsStream::url('root/annotations.cache'));
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        AnnotationCache::stop();
    }

    /**
     * @test
     */
    public function noAnnotationAddedDoesNotWriteCacheFile()
    {
        AnnotationCache::__shutdown();
        assertFalse(file_exists(vfsStream::url('root/annotations.cache')));
    }

    /**
     * @test
     */
    public function addingAnnotationWritesCacheFile()
    {
        $annotations = new Annotations('someTarget');
        AnnotationCache::put($annotations);
        AnnotationCache::__shutdown();
        assertTrue(file_exists(vfsStream::url('root/annotations.cache')));
    }

    /**
     * @test
     */
    public function cacheFileContainsSerializedAnnotationData()
    {
        $annotations = new Annotations('someTarget');
        AnnotationCache::put($annotations);
        AnnotationCache::__shutdown();
        $data = unserialize(file_get_contents(vfsStream::url('root/annotations.cache')));
        assert($data, hasKey('someTarget'));
        assert(unserialize($data['someTarget']), equals($annotations));
    }

    /**
     * @since  3.0.0
     * @group  issue_58
     * @test
     */
    public function stoppingAnnotationPersistenceDoesNotWriteCacheFileOnShutdown()
    {
        AnnotationCache::put(new Annotations('someTarget'));
        AnnotationCache::stop();
        AnnotationCache::__shutdown();
        assertFalse(file_exists(vfsStream::url('root/annotations.cache')));
    }

    /**
     * @test
     */
    public function retrieveAnnotationsForUncachedTargetReturnsNull()
    {
        assertNull(AnnotationCache::get('DoesNotExist'));
    }

    /**
     * @since  3.0.0
     * @group  issue_58
     * @test
     */
    public function startAnnotationCacheWithInvalidCacheDataThrowsRuntimeException()
    {
        expect(function() {
                AnnotationCache::start(function() { return serialize('foo'); }, function() {});
        })
        ->throws(\RuntimeException::class);
    }

    /**
     * @since  3.0.0
     * @group  issue_58
     * @test
     */
    public function startAnnotationCacheWithNonSerializedCacheDataThrowsRuntimeException()
    {
        expect(function() {
                AnnotationCache::start(function() { return 'foo'; }, function() {});
        })
        ->throws(\RuntimeException::class);
    }
}
