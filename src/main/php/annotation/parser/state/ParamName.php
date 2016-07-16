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
 * Parser is inside an annotation param name.
 *
 * @internal
 */
class ParamName implements AnnotationState
{
    /**
     * list of tokens which signal that a word must be processed
     *
     * @type  array
     */
    public $signalTokens = [
            "'" => AnnotationState::PARAM_VALUE_IN_SINGLE_QUOTES,
            '"' => AnnotationState::PARAM_VALUE_IN_DOUBLE_QUOTES,
            '=' => AnnotationState::PARAM_VALUE,
            ')' => AnnotationState::DOCBLOCK,
    ];

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
        $paramName = trim(ltrim(trim($word->content), ',*'));
        if ("'" === $currentToken || '"' === $currentToken) {
            if (strlen($paramName) > 0) {
                throw new \ReflectionException('Annotation parameter name may contain letters, underscores and numbers, but contains ' . $currentToken . '. Probably an equal sign is missing: ' . $paramName);
            }

            $annotation->currentParam = '__value';
        } elseif ('=' === $currentToken) {
            if (strlen($paramName) == 0) {
                throw new \ReflectionException('Annotation parameter name has to start with a letter or underscore, but starts with =: ' . $paramName);
            } elseif (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $paramName) == false) {
                throw new \ReflectionException('Annotation parameter name may contain letters, underscores and numbers, but contains an invalid character: ' . $paramName);
            }

            $annotation->currentParam = $paramName;
        } elseif (')' === $currentToken) {
            if (strlen($paramName) > 0) {
                if (count($annotation->params) > 0) {
                    throw new \ReflectionException(
                        'Error in annotation ' . $annotation->type
                        . ', contains value "' . $paramName
                        . '" without a name after named values'
                    );
                }

                $annotation->params['__value'] = $paramName;
            }
        }

        return true;
    }
}
