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
 * Parser is inside the annotation name.
 *
 * @internal
 */
class AnnotationName extends Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @var  array<string,Expression>
     */
    public array $after = [];

    public function init(): void
    {
        $this->after = [
                ' '  => self::$ANNOTATION,
                "\n" => self::$DOCBLOCK,
                "\r" => self::$DOCBLOCK,
                '{'  => self::$ARGUMENT,
                '['  => self::$ANNOTATION_TYPE,
                '('  => self::$PARAM_NAME
        ];
    }

    /**
     * list of forbidden annotation names
     *
     * @var  array<string,int>
     */
    private array $forbiddenAnnotationNames = [
            'deprecated'     => 1,
            'example'        => 1,
            'ignore'         => 1,
            'internal'       => 1,
            'link'           => 1,
            'method'         => 1,
            'package'        => 1,
            'param'          => 1,
            'property'       => 1,
            'property-read'  => 1,
            'property-write' => 1,
            'return'         => 1,
            'see'            => 1,
            'since'          => 1,
            'static'         => 1,
            'subpackage'     => 1,
            'throws'         => 1,
            'todo'           => 1,
            'type'           => 1,
            'uses'           => 1,
            'var'            => 1,
            'version'        => 1,
            'api'            => 1,
            'psalm-param'    => 1,
    ];

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        if (empty($token->value)) {
            throw new \ReflectionException(
                    'Annotation name for ' . $annotation . ' can not be empty'
            );
        }

        if (isset($this->forbiddenAnnotationNames[$token->value])) {
            $annotation->ignored = true;
            return false;
        }

        if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $token->value) == false) {
            throw new \ReflectionException(
                    'Annotation name for ' . $annotation . ' must start with a'
                    . ' letter or underscore and may contain letters, underscores'
                    . ' and numbers, but contains an invalid character: @'
                    . $token->value
            );
        }

        $annotation->type = $annotation->name = $token->value;
        return true;
    }
}
