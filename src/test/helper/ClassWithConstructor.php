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
 * Helper class for the test.
 */
#[SomeClassAttribute(303)]
#[SomeClassAttribute(404)]
#[AnotherClassAttribute]
class ClassWithConstructor
{
    /** @var  int */
    private $example;

    /**
     * @SomeAnnotation{example}
     */
    public function __construct(int $example)
    {
        $this->example = $example;
    }
}
