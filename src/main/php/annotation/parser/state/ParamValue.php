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
namespace stubbles\reflect\annotation\parser\state;
/**
 * Parser is inside an annotation param value.
 *
 * @internal
 */
class ParamValue implements AnnotationState
{
    /**
     * list of tokens which signal that a word must be processed
     *
     * @type  array
     */
    public $signalTokens = [
            "'" => AnnotationState::PARAM_VALUE_IN_SINGLE_QUOTES,
            '"' => AnnotationState::PARAM_VALUE_IN_DOUBLE_QUOTES,
            ',' => AnnotationState::PARAM_NAME,
            ')' => AnnotationState::DOCBLOCK
    ];

    /**
     * processes a token
     *
     * @param   string             $word          parsed word to be processed
     * @param   string             $currentToken  current token that signaled end of word
     * @param   CurrentAnnotation  $annotation    currently parsed annotation
     * @return  bool
     */
    public function process($word, string $currentToken, CurrentAnnotation $annotation): bool
    {
        if (',' === $currentToken || ')' === $currentToken) {
            $annotation->params[$annotation->currentParam] = $word->content;
        }

        return true;
    }
}
