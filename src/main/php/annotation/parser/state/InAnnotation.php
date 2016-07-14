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
 * Parser is inside the annotation.
 *
 * @internal
 */
class InAnnotation extends AnnotationAbstractState implements AnnotationState
{
    /**
     * list of tokens which signal that a word must be processed
     *
     * @type  array
     */
    public $signalTokens = [
            "\n" => AnnotationState::DOCBLOCK,
            '{'  => AnnotationState::ARGUMENT,
            '['  => AnnotationState::ANNOTATION_TYPE,
            '('  => AnnotationState::PARAM_NAME
    ];

    /**
     * processes a token
     *
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @return  bool
     */
    public function process(string $word, string $currentToken): bool
    {
        if ("\n" === $currentToken) {
            $this->parser->changeState(AnnotationState::DOCBLOCK);
        } elseif ('{' === $currentToken) {
            $this->parser->changeState(AnnotationState::ARGUMENT);
        } elseif ('[' === $currentToken) {
            $this->parser->changeState(AnnotationState::ANNOTATION_TYPE);
        } elseif ('(' === $currentToken) {
            $this->parser->changeState(AnnotationState::PARAM_NAME);
        }

        return true;
    }
}
