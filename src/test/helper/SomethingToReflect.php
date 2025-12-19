<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\test\helper;
/**
 * Helper interface for the test.
 */
interface SomethingToReflect
{
    function something(string $foo): void;
}