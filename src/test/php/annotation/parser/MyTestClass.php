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
 * This is a test class that has many annotations.
 *
 * @Foo
 * @FooWithBrackets ()
 * @Bar[TomTom]
 * @MyAnnotation(foo='bar')
 * @TwoParams(foo='bar', test=42)
 * @InvalidChars(foo='ba@r=,')
 * @Constant(foo=stubbles\reflect\annotation\parser\MyTestClass::TEST_CONSTANT)
 * @WithEscaped(foo='This string contains \' and \, which is possible using escaping...')
 * @Multiline(one=1,
 *            two=2)
 * @Class(stubbles\reflect\annotation\parser\MyTestClass.class)
 */
class MyTestClass
{
    const TEST_CONSTANT = 'baz';
}