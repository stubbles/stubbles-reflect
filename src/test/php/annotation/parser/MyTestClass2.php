<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation\parser;
class MyTestClass2
{
    /**
     * a method with an annotation for its parameter
     *
     * @param  string  $bar
     * @ForArgument1{bar}
     * @ForArgument2{bar}(key='value')
     * @MoreArgument1{bar}[Casted]
     * @MoreArgument2{bar}[Casted](key='value')
     * @MoreArgument3[CastedAround]{bar}
     * @MoreArgument4[CastedAround]{bar}(key='value')
     * @another
     */
    public function foo(string $bar): void { }
}