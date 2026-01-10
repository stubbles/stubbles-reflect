<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\test\helper;

use Attribute;

/**
 * @since 11.1.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class SomeClassAttribute
{
    public function __construct(public readonly int $value)
    {
        
    }
}
