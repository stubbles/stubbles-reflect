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
 * Parser is inside an enclosed annotation param value.
 *
 * @internal
 */
class EnclosedParamValue extends Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @type  array
     */
    public $after;

    public function init(string $quoteCharacter)
    {
        $this->after = [$quoteCharacter => self::$PARAM_NAME];
    }

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        if (substr($token->value, -1) === '\\') {
            $token->value = rtrim($token->value, '\\') . $signal;
            return false;
        }

        $annotation->params[$annotation->currentParam] = $token->value;
        return true;
    }
}
