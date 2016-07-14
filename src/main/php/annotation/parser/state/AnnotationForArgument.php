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
 * Parser is inside the annotation argument.
 *
 * @internal
 */
class AnnotationForArgument extends AnnotationAbstractState implements AnnotationState
{
    /**
     * list of tokens which signal that a word must be processed
     *
     * @type  array
     */
    public $signalTokens = ['}' => AnnotationState::ANNOTATION];

    /**
     * processes a token
     *
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @return  bool
     * @throws  \ReflectionException
     */
    public function process(string $word, string $currentToken): bool
    {
        if (empty($word)) {
            throw new \ReflectionException('Argument name for annotation is empty.');
        }

        if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $word) == false) {
            throw new \ReflectionException('Argument name for annotation may contain letters, underscores and numbers, but contains an invalid character.');
        }

        $this->parser->markAsParameterAnnotation($word);
        $this->parser->changeState(AnnotationState::ANNOTATION);
        return true;
    }
}
