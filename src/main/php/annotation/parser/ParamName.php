<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation\parser;
/**
 * Parser is inside an annotation param name.
 *
 * @internal
 */
class ParamName extends Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @var  array<string,Expression>
     */
    public $after;

    public function init(): void
    {
        $this->after = [
                "'" => self::$PARAM_VALUE_IN_SINGLE_QUOTES,
                '"' => self::$PARAM_VALUE_IN_DOUBLE_QUOTES,
                '=' => self::$PARAM_VALUE,
                ')' => self::$DOCBLOCK,
        ];
    }

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        $paramName = trim(ltrim(trim($token->value), ",\n\r *"));
        if (("'" === $signal || '"' === $signal) && strlen($paramName) > 0) {
            throw new \ReflectionException(
                    'Annotation parameter "' . $paramName . '" for ' . $annotation
                    . ' may contain letters, underscores and numbers, but contains '
                    . $signal . '. Probably an equal sign is missing.'
            );
        }

        if ('=' === $signal) {
            if (strlen($paramName) == 0) {
                throw new \ReflectionException(
                        'Annotation parameter for ' . $annotation . ' has to'
                        . ' start with a letter or underscore, but starts with "="'
                );
            }

            if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $paramName) == false) {
                throw new \ReflectionException(
                        'Annotation parameter for ' . $annotation . ' must start'
                        . ' with a letter or underscore and contain letters,'
                        . ' underscores and numbers, but contains an invalid'
                        . ' character: ' . $paramName
                );
            }

            $annotation->currentParam = $paramName;
        } elseif (')' === $signal) {
            if (strlen($paramName) > 0) {
                if (count($annotation->params) > 0) {
                    if (isset($annotation->params[CurrentAnnotation::SINGLE_VALUE])) {
                        $message = 'Error in annotation ' . $annotation . ','
                                 . ' contains two values without name.';
                    } else {
                        $message = 'Error in annotation ' . $annotation . ','
                                 . ' contains value "' . $paramName . '" without'
                                 . ' a name after named values';
                    }

                    throw new \ReflectionException($message);
                }

                $annotation->params[$annotation->currentParam] = $paramName;
            }
        }

        return true;
    }
}
