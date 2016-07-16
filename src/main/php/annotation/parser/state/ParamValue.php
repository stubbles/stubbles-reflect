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
class ParamValue extends AnnotationAbstractState implements AnnotationState
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
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @return  bool
     */
    public function process($word, string $currentToken): bool
    {
        if (strlen($word->content) === 0 && ('"' === $currentToken || "'" === $currentToken)) {
            if ("'" === $currentToken) {
                $this->parser->changeState(AnnotationState::PARAM_VALUE_IN_SINGLE_QUOTES);
            } else {
                $this->parser->changeState(AnnotationState::PARAM_VALUE_IN_DOUBLE_QUOTES);
            }
        } elseif (',' === $currentToken) {
            $this->parser->setAnnotationParamValue($word->content);
            $this->parser->changeState(AnnotationState::PARAM_NAME);
        } elseif (')' === $currentToken) {
            $this->parser->setAnnotationParamValue($word->content);
            $this->parser->changeState(AnnotationState::DOCBLOCK);
        }

        return true;
    }
}
