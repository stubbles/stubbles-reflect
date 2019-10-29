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
 * Information about the currently parsed annotation.
 *
 * @internal
 */
class CurrentAnnotation
{
    const SINGLE_VALUE = '__value';
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
    public $currentParam = self::SINGLE_VALUE;
    /**
     * annotation target
     *
     * @type  string
     */
    public $target;
    /**
     * name of parameter when annotation is for function/method parameter
     *
     * @type  string
     */
    public $targetParam;
    /**
     * whether annotation must be ignored, i.e. because it's a phpdoc one
     *
     * @type  bool
     */
    public $ignored      = false;
    /**
     * original target when parser detects its an annotation for a parameter
     *
     * @type  string
     */
    private $originalTarget;

    public function __construct(string $target)
    {
        $this->target         = $target;
        $this->originalTarget = $target;
    }

    public function __toString(): string
    {
        $return = sprintf(
                '%s@%s%s%s',
                $this->originalTarget,
                $this->name,
                $this->type != $this->name ? '[' . $this->type . ']' : '',
                null !== $this->targetParam ? '{' . $this->targetParam . '}' : ''
        );

        if (count($this->params) > 0) {
            $return .= '(';
            if (isset($this->params[self::SINGLE_VALUE])) {
                $return .= $this->params[self::SINGLE_VALUE];
            } else {
                $params = [];
                foreach ($this->params as $name => $value) {
                    $params[] = $name . '=' . $value;
                }

                $return .= join(', ', $params);
            }

            $return .= ')';
        }

        return $return;
    }
}
