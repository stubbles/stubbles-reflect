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
    public $signalTokens = ["'" => 0, '"' => 1, '\\' => 2];
    /**
     * character in which the value is enclosed
     *
     * @type  string
     */
    private $enclosed  = null;
    /**
     * whether next character is escaped
     *
     * @type  bool
     */
    private $escaped   = false;
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
     * @param   string  $nextToken     next token after current token
     * @return  bool
     */
    public function process(string $word, string $currentToken, string $nextToken): bool
    {
        if (strlen($this->collected) === 0 && strlen($word) === 0 && ('"' === $currentToken || "'" === $currentToken)) {
            $this->enclosed = $currentToken;
            if ('""' === $currentToken) {
                unset($this->signalTokens["'"]);
            } else {
                unset($this->signalTokens['"']);
            }
        } elseif (!$this->escaped && $this->enclosed === $currentToken) {
            $this->parser->setAnnotationParamValue($this->collected . $word);
            $this->signalTokens = ["'" => 0, '"' => 1, '\\' => 2];
            $this->enclosed     = null;
            $this->escaped      = false;
            $this->collected    = '';
            $this->parser->changeState(AnnotationState::PARAM_NAME);
        } elseif (!$this->escaped && '\\' === $currentToken && null !== $this->enclosed) {
            $this->escaped = true;
            return false;
        } elseif ($this->escaped) {
            $this->collected .= $word . $currentToken;
            $this->escaped = false;
        }

        return true;
    }
}
