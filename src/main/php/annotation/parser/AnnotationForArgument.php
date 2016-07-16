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
 * Parser is inside the annotation argument.
 *
 * @internal
 */
class AnnotationForArgument implements Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @type  array
     */
    public $after = ['}' => Expression::ANNOTATION];

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        if (empty($token->value)) {
            throw new \ReflectionException('Argument name for annotation is empty.');
        }

        if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $token->value) == false) {
            throw new \ReflectionException('Argument name for annotation may contain letters, underscores and numbers, but contains an invalid character.');
        }

        $annotation->target .= '#' . $token->value;
        return true;
    }
}
