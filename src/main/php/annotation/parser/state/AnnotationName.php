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
 * Parser is inside the annotation name.
 *
 * @internal
 */
class AnnotationName extends AnnotationAbstractState implements AnnotationState
{
    /**
     * list of tokens which signal that a word must be processed
     *
     * @type  array
     */
    public $signalTokens = [
            ' '  => AnnotationState::ANNOTATION,
            "\n" => AnnotationState::DOCBLOCK,
            "\r" => AnnotationState::DOCBLOCK,
            '{'  => AnnotationState::ARGUMENT,
            '['  => AnnotationState::ANNOTATION_TYPE,
            '('  => AnnotationState::PARAM_NAME
    ];
    /**
     * list of forbidden annotation names
     *
     * @type  string[]
     */
    protected $forbiddenAnnotationNames = [
            'deprecated'     => 1,
            'example'        => 1,
            'ignore'         => 1,
            'internal'       => 1,
            'link'           => 1,
            'method'         => 1,
            'package'        => 1,
            'param'          => 1,
            'property'       => 1,
            'property-read'  => 1,
            'property-write' => 1,
            'return'         => 1,
            'see'            => 1,
            'since'          => 1,
            'static'         => 1,
            'subpackage'     => 1,
            'throws'         => 1,
            'todo'           => 1,
            'type'           => 1,
            'uses'           => 1,
            'var'            => 1,
            'version'        => 1,
            'api'            => 1
    ];

    /**
     * processes a token
     *
     * @param   string  $word          parsed word to be processed
     * @param   string  $currentToken  current token that signaled end of word
     * @return  bool
     * @throws  \ReflectionException
     */
    public function process($word, string $currentToken): bool
    {
        if (strlen($word->content) > 0) {
            if (isset($this->forbiddenAnnotationNames[$word->content])) {
                $this->parser->changeState(AnnotationState::DOCBLOCK);
                return true;
            }

            if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $word->content) == false) {
                throw new \ReflectionException(
                        'Annotation parameter name may contain letters, underscores '
                        . 'and numbers, but contains an invalid character: '
                        . $word->content
                );
            }

            $this->parser->registerAnnotation($word->content);
        }

        if (' ' === $currentToken) {
            if (empty($word->content)) {
                $this->parser->changeState(AnnotationState::DOCBLOCK);
            } else {
                $this->parser->changeState(AnnotationState::ANNOTATION);
            }
        } elseif ("\n" === $currentToken || "\r" === $currentToken) {
            $this->parser->changeState(AnnotationState::DOCBLOCK);
        } elseif ('{' === $currentToken) {
            if (empty($word->content)) {
                throw new \ReflectionException('Annotation name can not be empty');
            }

            $this->parser->changeState(AnnotationState::ARGUMENT);
        } elseif ('[' === $currentToken) {
            if (empty($word->content)) {
                throw new \ReflectionException('Annotation name can not be empty');
            }

            $this->parser->changeState(AnnotationState::ANNOTATION_TYPE);
        } elseif ('(' === $currentToken) {
            if (empty($word->content)) {
                throw new \ReflectionException('Annotation name can not be empty');
            }

            $this->parser->changeState(AnnotationState::PARAM_NAME);
        }

        return true;
    }
}
