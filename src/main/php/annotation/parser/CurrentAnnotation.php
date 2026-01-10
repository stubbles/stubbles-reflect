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
 * @deprecated since 11.1.0, will be removed with 12.0.0, use attributes instead
 */
class CurrentAnnotation
{
    const SINGLE_VALUE = '__value';
    /**
     * the name of the current annotation
     */
    public string $name = '';
    /**
     * actual type
     */
    public string $type = '';
    /**
     * map of parameters
     *
     * @var  array<string,string>
     */
    public array $params       = [];
    /**
     * the name of the current annotation parameter
     */
    public string $currentParam = self::SINGLE_VALUE;
    /**
     * name of parameter when annotation is for function/method parameter
     */
    public ?string $targetParam = null;
    /**
     * whether annotation must be ignored, i.e. because it's a phpdoc one
     */
    public bool $ignored      = false;
    /**
     * original target when parser detects its an annotation for a parameter
     */
    private string $originalTarget;

    public function __construct(public string $target)
    {
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
