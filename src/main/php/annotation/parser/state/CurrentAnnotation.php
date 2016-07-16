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
/**
 * Information about the currently parsed annotation.
 *
 * @internal
 */
class CurrentAnnotation
{
    /**
     * the name of the current annotation
     *
     * @type  string
     */
    public $name;
    /**
     * actual type
     *
     * @type  string
     */
    public $type;
    /**
     * map of parameters
     *
     * @type  array
     */
    public $params       = [];
    /**
     * the name of the current annotation parameter
     *
     * @type  string
     */
    public $currentParam = null;
    /**
     * annotation target
     *
     * @param  string
     */
    public $target;
    /**
     * whether annotation must be ignored, i.e. because it's a phpdoc one
     *
     * @type  bool
     */
    public $ignored      = false;

    public function __construct(string $target)
    {
        $this->target = $target;
    }
}
