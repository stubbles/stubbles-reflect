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
 * Parser is inside the annotation.
 *
 * @internal
 */
class InAnnotation implements Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @type  array
     */
    public $after = [
            "\n" => Expression::DOCBLOCK,
            '{'  => Expression::ARGUMENT,
            '['  => Expression::ANNOTATION_TYPE,
            '('  => Expression::PARAM_NAME
    ];

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        return true;
    }
}
