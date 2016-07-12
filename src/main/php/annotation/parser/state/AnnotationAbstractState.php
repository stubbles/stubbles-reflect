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
namespace stubbles\reflect\annotation\parser\state;
use stubbles\reflect\annotation\parser\AnnotationParser;
/**
 * Abstract base class for annotion parser states.
 *
 * @internal
 */
abstract class AnnotationAbstractState
{
    /**
     * the parser this state belongs to
     *
     * @type  \stubbles\reflect\annotation\parser\AnnotationParser
     */
    protected $parser;

    /**
     * constructor
     *
     * @param  \stubbles\reflect\annotation\parser\AnnotationParser  $parser
     */
    public function __construct(AnnotationParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * mark this state as the currently used state
     */
    public function selected()
    {
        // intentionally empty
    }
}
