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
use stubbles\reflect\annotation\parser\AnnotationParser;
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
    public $signalTokens;
    /**
     * character in which the value is enclosed
     *
     * @type  string
     */
    private $enclosed  = null;

    /**
     * constructor
     *
     * @param  \stubbles\reflect\annotation\parser\AnnotationParser  $parser
     * @param  string                                                $enclosed
     */
    public function __construct(AnnotationParser $parser, $enclosed)
    {
        parent::__construct($parser);
        $this->enclosed     = $enclosed;
        $this->signalTokens = [$enclosed => AnnotationState::PARAM_NAME];
    }

    /**
     * processes a token
     *
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @return  bool
     */
    public function process($word, string $currentToken): bool
    {
        if (substr($word->content, -1) === '\\') {
            $word->content = rtrim($word->content, '\\') . $currentToken;
            return false;
        }

        $this->parser->setAnnotationParamValue($word->content);
        return true;
    }
}
