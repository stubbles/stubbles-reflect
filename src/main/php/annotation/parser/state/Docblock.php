<?php
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
 * Parser is in docblock, but not in any annotation.
 *
 * @internal
 */
class Docblock implements AnnotationState
{
    /**
     * list of tokens which signal that a word must be processed
     *
     * @type  array
     */
    public $signalTokens = ['@' => AnnotationState::ANNOTATION_NAME];

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
        return true;
    }
}
