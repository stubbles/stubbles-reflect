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
 * Represents an expression that can be encountered during parsing.
 *
 * @internal
 */
interface Expression
{
    /**
     * parser is inside the standard docblock
     */
    const DOCBLOCK                     = 1;
    /**
     * parser is inside an annotation
     */
    const ANNOTATION                   = 2;
    /**
     * parser is inside an annotation name
     */
    const ANNOTATION_NAME              = 3;
    /**
     * parser is inside an annotation type
     */
    const ANNOTATION_TYPE              = 4;
    /**
     * parser is inside an annotation param name
     */
    const PARAM_NAME                   = 5;
    /**
     * parser is inside an annotation param value
     */
    const PARAM_VALUE                  = 6;
    /**
     * parser is inside a argument declaration
     */
    const ARGUMENT                     = 7;
    /**
     * parser is inside an enclosed annotation param value
     */
    const PARAM_VALUE_IN_SINGLE_QUOTES = 8;
    /**
     * parser is inside an enclosed annotation param value
     */
    const PARAM_VALUE_IN_DOUBLE_QUOTES = 9;

    /**
     * evaluates a token and the detected signal into the annotation
     *
     * @param   Token              $token       parsed token to be processed
     * @param   string             $signal      signal encountered by parser
     * @param   CurrentAnnotation  $annotation  currently parsed annotation
     * @return  bool
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool;
}
