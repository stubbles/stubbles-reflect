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
class AnnotationAnnotationState extends AnnotationAbstractState implements AnnotationState
{
    /**
     * returns list of tokens that signal state change
     *
     * @return  string[]
     */
    public function signalTokens(): array
    {
        return ["\n", '{', '[', '('];
    }

    /**
     * processes a token
     *
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @param   string  $nextToken     next token after current token
     * @return  bool
     */
    public function process(string $word, string $currentToken, string $nextToken): bool
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
