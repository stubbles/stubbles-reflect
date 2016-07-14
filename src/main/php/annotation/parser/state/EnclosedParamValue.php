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
 * Parser is inside an enclosed annotation param value.
 *
 * @internal
 */
class EnclosedParamValue extends AnnotationAbstractState implements AnnotationState
{
    /**
     * list of tokens which signal that a word must be processed
     *
     * @type  array
     */
    public $signalTokens = [
            "'" => AnnotationState::PARAM_NAME,
            '"' => AnnotationState::PARAM_NAME
    ];
    /**
     * character in which the value is enclosed
     *
     * @type  string
     */
    private $enclosed  = null;
    /**
     * collected value until an escaping sign occurred
     *
     * @type  string
     */
    private $collected = '';

    /**
     * processes a token
     *
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @return  bool
     */
    public function process(string $word, string $currentToken): bool
    {
        if (strlen($this->collected) === 0 && strlen($word) === 0) {
            $this->enclosed = $currentToken;
        } elseif ($this->enclosed === $currentToken && substr($word, -1) === '\\') {
            $this->collected .= substr($word, 0, strlen($word) - 1) . $currentToken;
        } elseif ($this->enclosed === $currentToken) {
            $this->parser->setAnnotationParamValue($this->collected . $word);
            $this->enclosed     = null;
            $this->collected    = '';
            $this->parser->changeState(AnnotationState::PARAM_NAME);
        } else {
            $this->collected .= $word . $currentToken;
        }

        return true;
    }
}
