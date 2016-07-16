<?php
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
 * Parser is in docblock, but not in any annotation.
 *
 * @internal
 */
class Docblock extends Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @type  array
     */
    public $after;

    public function init()
    {
        $this->after = ['@' => self::$ANNOTATION_NAME];
    }

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        return true;
    }
}
