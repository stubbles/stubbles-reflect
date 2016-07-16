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
namespace stubbles\reflect\annotation\parser;
/**
 * Parser is inside an annotation param value.
 *
 * @internal
 */
class ParamValue implements Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @type  array
     */
    public $after = [
            "'" => Expression::PARAM_VALUE_IN_SINGLE_QUOTES,
            '"' => Expression::PARAM_VALUE_IN_DOUBLE_QUOTES,
            ',' => Expression::PARAM_NAME,
            ')' => Expression::DOCBLOCK
    ];

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
