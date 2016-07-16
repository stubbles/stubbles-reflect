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
namespace stubbles\reflect\annotation\parser;
/**
 * Interface for parsers to parse Java-Style annotations.
 *
 * @internal
 */
interface AnnotationParser
{
    /**
     * change the current state
     *
     * @param  int     $state
     */
    public function changeState(int $state);

    /**
     * parse a docblock and return all annotations found
     *
     * @param   string  $docComment
     * @param   string  $target
     * @return  \stubbles\reflect\annotation\Annotations[]
     */
    public function parse(string $docComment, string $target): array;

    /**
     * register a new annotation
     *
     * @param  string  $name
     */
    public function registerAnnotation(string $name);

    /**
     * register a new annotation param
     *
     * @param  string  $name
     */
    public function registerAnnotationParam(string $name);

    /**
     * register single annotation param
     *
     * @param   string  $value  the value of the param
     * @throws  \ReflectionException
     */
    public function registerSingleAnnotationParam(string $value);

    /**
     * set the annoation param value for the current annotation
     *
     * @param   string  $value  the value of the param
     * @throws  \ReflectionException
     */
    public function setAnnotationParamValue(string $value);

    /**
     * set the type of the current annotation
     *
     * @param  string  $type  type of the annotation
     */
    public function setAnnotationType(string $type);

    /**
     * marks the current annotation as being an annotation for a function/method parameter
     *
     * @param  string  $parameterName  name of the parameter
     */
    public function markAsParameterAnnotation(string $parameterName);
}
