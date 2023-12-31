<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation;
use stubbles\reflect\annotation\parser\Parser;
/**
 * Contains a list of all annotations for a target.
 *
 * @since  5.0.0
 * @implements \IteratorAggregate<Annotation>
 */
class Annotations implements \IteratorAggregate, \Countable
{
    /**
     * list of annotation types and their instances
     *
     * @var  array<string,Annotation[]>
     */
    private array $types       = [];

    /**
     * parse a docblock and return all annotations found
     *
     * @param   string  $docComment
     * @param   string  $target
     * @return  \stubbles\reflect\annotation\Annotations[]
     * @since   8.0.0
     */
    public static function parse(string $docComment, string $target): array
    {
        static $parser = null;
        if (null === $parser) {
            $parser = new Parser();
        }

        return $parser->parse($docComment, $target);
    }

    public function __construct(private string $target) { }

    /**
     * adds given annotation
     *
     * @internal  only to be called by the parser
     */
    public function add(Annotation $annotation): self
    {
        if (!isset($this->types[$annotation->type()])) {
            $this->types[$annotation->type()] = [$annotation];
        } else {
            $this->types[$annotation->type()][] = $annotation;
        }

        return $this;
    }

    /**
     * target for which annotations are for
     */
    public function target(): string
    {
        return $this->target;
    }

    /**
     * checks if at least one annotation of given type is present
     *
     * @api
     */
    public function contain(string $type): bool
    {
        return isset($this->types[$type]);
    }

    /**
     * returns first annotation with given type name
     *
     * If no such annotation exists a ReflectionException is thrown.
     *
     * @throws  \ReflectionException
     * @since   5.3.0
     */
    public function firstNamed(string $type): Annotation
    {
        if ($this->contain($type)) {
            return $this->types[$type][0];
        }

        throw new \ReflectionException('Can not find annotation ' . $type . ' for ' . $this->target);
    }

    /**
     * returns a list of all annotations of this type
     *
     * @api
     * @return  \stubbles\reflect\annotation\Annotation[]
     * @since   5.3.0
     */
    public function named(string $type): array
    {
        if ($this->contain($type)) {
            return $this->types[$type];
        }

        return [];
    }

    /**
     * returns a list of all annotations
     *
     * @api
     * @return  \Generator<Annotation>
     */
    public function all(): \Generator
    {
        foreach ($this as $annotation) {
            yield $annotation;
        }
    }

    /**
     * returns an iterator to iterate over all annotations
     *
     * @return  \Traversable<Annotation>
     */
    public function getIterator(): \Traversable
    {
        return new \RecursiveIteratorIterator(
                new RecursiveArrayIterator($this->types)
        );
    }

    /**
     * returns amount of annotations
     *
     * @since   9.2.0
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->types as $annotations) {
            $count += count($annotations);
        }

        return $count;
    }
}
