<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation;
/**
 * Static cache for annotations
 *
 * @internal
 * @static
 */
class AnnotationCache
{
    /**
     * map of stored serialized annotations
     *
     * @var  string[]
     */
    private static $annotations  = [];
    /**
     * map of stored annotations
     *
     * @var  \stubbles\reflect\annotation\Annotations[]
     */
    private static $unserialized = [];
    /**
     * flag whether cache contents changed
     *
     * @var  bool
     */
    private static $cacheChanged = false;
    /**
     * closure which stores the current annotation cache
     *
     * @var  callable|null
     */
    private static $storeCache;

    /**
     * start annotation cache with given cache storage logic
     *
     * Calling this method will also flush the cache. If this method is never
     * called the annotation cache will not be persistent but only last as long
     * as the current request is running.
     *
     * The $readCache closure must return the stored annotation data. If no such
     * data is present it must return null. In case the stored annotation data
     * can't be unserialized into an array a \RuntimeException will be thrown.
     *
     * The $storeCache closure must store passed annotation data. It doesn't
     * need to take care about serialization, as it already receives a
     * serialized representation.
     *
     * A possible implementation for the file cache would look like this:
     * <code>
     * AnnotationCache::start(
     *      function() use($cacheFile)
     *      {
     *          if (file_exists($cacheFile)) {
     *              return unserialize(file_get_contents($cacheFile));
     *          }
     *
     *          return [];
     *      },
     *      function(array $annotationData) use($cacheFile)
     *      {
     *          file_put_contents($cacheFile, serialize($annotationData));
     *      }
     * );
     * </code>
     *
     * @param   callable  $readCache   function which can return cached annotation data
     * @param   callable  $storeCache  function which takes cached annotation data and stores it
     * @throws  \RuntimeException
     * @since   3.0.0
     */
    public static function start(callable $readCache, callable $storeCache): void
    {
        self::$annotations = $readCache();
        if (!is_array(self::$annotations)) {
            self::flush();
            throw new \RuntimeException('Cached annotation data is not an array');
        }

        self::$unserialized = [];
        self::$cacheChanged = false;
        self::$storeCache   = $storeCache;
        $shutdown = [__CLASS__, '__shutdown'];
        /** @var  callable(): void  $shutdown */
        register_shutdown_function($shutdown);
    }

    /**
     * starts annotation cache with given cache file
     *
     * @param  string  $cacheFile  path to file wherein cached annotation data is stored
     * @since  3.0.0
     */
    public static function startFromFileCache(string $cacheFile): void
    {
        self::start(
                function() use($cacheFile)
                {
                    if (file_exists($cacheFile)) {
                        $contents = file_get_contents($cacheFile);
                        if (false !== $contents) {
                          return unserialize($contents);
                        }
                    }

                    return [];
                },
                function(array $annotationData) use($cacheFile)
                {
                    file_put_contents($cacheFile, serialize($annotationData));
                }
        );
    }

    /**
     * stops annotation cache persistence
     *
     * @since  3.0.0
     */
    public static function stop(): void
    {
        self::$storeCache = null;
    }

    /**
     * static shutdown
     */
    public static function __shutdown(): void
    {
        if (self::$cacheChanged && null !== self::$storeCache) {
            $storeCache = self::$storeCache;
            $storeCache(self::$annotations);
        }
    }

    /**
     * flushes all contents from cache
     */
    public static function flush(): void
    {
        self::$annotations  = [];
        self::$unserialized = [];
        self::$cacheChanged = true;
    }

    /**
     * store annotations in the cache
     *
     * @param  \stubbles\reflect\annotation\Annotations  $annotations
     */
    public static function put(Annotations $annotations): void
    {
        self::$annotations[$annotations->target()]  = serialize($annotations);
        self::$unserialized[$annotations->target()] = $annotations;
        self::$cacheChanged = true;
    }

    /**
     * check, whether annotations are available in the cache
     *
     * @param   string  $target  name of the target
     * @return  bool
     */
    public static function has(string $target): bool
    {
        return isset(self::$annotations[$target]);
    }

    /**
     * returns list of all annotations for given target
     *
     * @param   string  $target
     * @return  \stubbles\reflect\annotation\Annotations
     */
    public static function get(string $target): Annotations
    {
        if (!self::has($target)) {
            return new Annotations($target);
        }

        if (!isset(self::$unserialized[$target])) {
            self::$unserialized[$target] = unserialize(self::$annotations[$target]);
        }

        return self::$unserialized[$target];
    }
}
