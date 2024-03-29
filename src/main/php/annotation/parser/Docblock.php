<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @var  array<string,Expression>
     */
    public array $after = [];

    public function init(): void
    {
        $this->after = ['@' => self::$ANNOTATION_NAME];
    }
}
