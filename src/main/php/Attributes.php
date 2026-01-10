<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

/**
 * @since 11.1.0
 */
class Attributes
{
    /**
     * @var array<class-string,ReflectionAttribute[]>
     */
    private array $reflectionAttributes = [];
    /**
     * @param ReflectionAttribute[] $reflectionAttributes
     */
    public function __construct(
        array $reflectionAttributes,
        private string $target
    ) {
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $type = $reflectionAttribute->getName();
            if (!isset($this->reflectionAttributes[$type])) {
                $this->reflectionAttributes[$type] = [];
            }

            $this->reflectionAttributes[$type][] = $reflectionAttribute;
        }
    }

    public static function createFrom(Reflector $reflector, string $target)
    {
        if (
            $reflector instanceof ReflectionClass
            || $reflector instanceof ReflectionFunctionAbstract
            || $reflector instanceof ReflectionProperty
            || $reflector instanceof ReflectionParameter
            || $reflector instanceof ReflectionClassConstant
        ) {
            return new self($reflector->getAttributes(), $target);
        }

        throw new ReflectionException('Can not retrieve attributes for ' . get_class($reflector));
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @api
     */
    public function contain(string $type): bool
    {
        return isset($this->reflectionAttributes[$type]);
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T
     * @api
     */
    public function firstNamed(string $type): object
    {
        if ($this->contain($type)) {
            return $this->reflectionAttributes[$type][0]->newInstance();
        }

        throw new ReflectionException('Can not find attribute #[' . $type . '] for ' . $this->target);
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T[]
     * @api
     */
    public function named(string $type): array
    {
        if ($this->contain($type)) {
            return array_map(
                fn(ReflectionAttribute $attribute): object => $attribute->newInstance(),
                $this->reflectionAttributes[$type]
            );
        }

        return [];
    }

    /**
     * @api
     */
    public function isEmpty(): bool
    {
        return empty($this->reflectionAttributes);
    }
}
