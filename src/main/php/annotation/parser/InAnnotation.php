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
 * Parser is inside the annotation.
 *
 * @internal
 */
class InAnnotation extends Expression
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
                "\n" => self::$DOCBLOCK,
                '{'  => self::$ARGUMENT,
                '['  => self::$ANNOTATION_TYPE,
                '('  => self::$PARAM_NAME
        ];
    }
}
