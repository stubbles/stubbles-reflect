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
 * Parser is inside the annotation type.
 *
 * @internal
 */
class AnnotationType extends AnnotationAbstractState implements AnnotationState
{
    /**
     * returns list of tokens that signal state change
     *
     * @return  string[]
     */
    public function signalTokens(): array
    {
        return [']'];
    }

    /**
     * processes a token
     *
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @param   string  $nextToken     next token after current token
     * @return  bool
     * @throws  \ReflectionException
     */
    public function process(string $word, string $currentToken, string $nextToken): bool
    {
        if (empty($word)) {
            throw new \ReflectionException('Annotation type can not be empty.');
        }

        if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $word) == false) {
            throw new \ReflectionException('Annotation type may contain letters, underscores and numbers, but contains an invalid character.');
        }

        $this->parser->setAnnotationType($word);
        $this->parser->changeState(AnnotationState::ANNOTATION);
        return true;
    }
}
