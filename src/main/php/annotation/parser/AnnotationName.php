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
namespace stubbles\reflect\annotation\parser;
/**
 * Parser is inside the annotation name.
 *
 * @internal
 */
class AnnotationName implements Expression
{
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @type  array
     */
    public $after = [
            ' '  => Expression::ANNOTATION,
            "\n" => Expression::DOCBLOCK,
            "\r" => Expression::DOCBLOCK,
            '{'  => Expression::ARGUMENT,
            '['  => Expression::ANNOTATION_TYPE,
            '('  => Expression::PARAM_NAME
    ];
    /**
     * list of forbidden annotation names
     *
     * @type  string[]
     */
    private $forbiddenAnnotationNames = [
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
            'api'            => 1
    ];

    /**
     * @inheritDoc
     */
    public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
    {
        if (empty($token->value)) {
            throw new \ReflectionException('Annotation name can not be empty');
        }

        if (isset($this->forbiddenAnnotationNames[$token->value])) {
            $annotation->ignored = true;
            return false;
        }

        if (preg_match('/^[a-zA-Z_]{1}[a-zA-Z_0-9]*$/', $token->value) == false) {
            throw new \ReflectionException(
                    'Annotation parameter name may contain letters, underscores '
                    . 'and numbers, but contains an invalid character: '
                    . $token->value
            );
        }

        $annotation->type = $annotation->name = $token->value;
        return true;
    }
}
