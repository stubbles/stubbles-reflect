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
 * Parser is inside an annotation param value.
 *
 * @internal
 * @deprecated since 11.1.0, will be removed with 12.0.0, use attributes instead
 */
class ParamValue extends Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @var  array<string,Expression>
     */
    public array $after = [];

    public function init(): void
    {
        $this->after = [
                "'" => self::$PARAM_VALUE_IN_SINGLE_QUOTES,
                '"' => self::$PARAM_VALUE_IN_DOUBLE_QUOTES,
                ',' => self::$PARAM_NAME,
                ')' => self::$DOCBLOCK
        ];
    }


    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        if (',' === $signal || ')' === $signal) {
            $annotation->params[$annotation->currentParam] = $token->value;
        }

        return true;
    }
}
