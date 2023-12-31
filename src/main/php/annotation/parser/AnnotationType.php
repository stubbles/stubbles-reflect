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
 * Parser is inside the annotation type.
 *
 * @internal
 */
class AnnotationType extends Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @var  array<string,Expression>
     */
    public array $after = [];

    public function init(): void
    {
        $this->after = [']' => self::$ANNOTATION];
    }

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        if (empty($token->value)) {
            throw new \ReflectionException(
                    'Annotation type for ' . $annotation . ' can not be empty.'
            );
        }

        if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $token->value) == false) {
            throw new \ReflectionException(
                    'Annotation type for ' . $annotation . ' must start with a'
                    . ' letter or underscore and may contain letters, underscores'
                    . ' and numbers, but contains an invalid character: '
                    . $token->value
            );
        }

        $annotation->type = $token->value;
        return true;
    }
}
