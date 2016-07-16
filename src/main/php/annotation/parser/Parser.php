<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\reflect\annotation\parser;
use stubbles\reflect\annotation\Annotation;
use stubbles\reflect\annotation\Annotations;
/**
 * Parser to parse annotations from doc comments.
 *
 * @internal
 */
class Parser
{
    /**
     * parse a docblock and return all annotations found
     *
     * @param   string  $docComment
     * @param   string  $target
     * @return  \stubbles\reflect\annotation\Annotations[]
     * @throws  \ReflectionException
     */
    public function parse(string $docComment, string $target): array
    {
        $annotations = [$target => new Annotations($target)];
        $annotation  = new CurrentAnnotation($target);
        $expression  = Expression::$DOCBLOCK;
        // substract last two characters which close the doc comment, i.e. */
        $len  = strlen($docComment) - 2;
        $token = new Token();
        for ($i = 6; $i < $len; $i++) {
            $character = $docComment{$i};
            if (isset($expression->after[$character])) {
                if ($expression->evaluate($token, $character, $annotation)) {
                    $token->value = '';
                    $expression   = $expression->after[$character];
                    if ($expression instanceof Docblock) {
                        if (null !== $annotation->name) {
                            if (!isset($annotations[$annotation->target])) {
                                $annotations[$annotation->target] = new Annotations($annotation->target);
                            }

                            $annotations[$annotation->target]->add(
                                    new Annotation(
                                            $annotation->type,
                                            $annotation->target,
                                            $annotation->params,
                                            $annotation->name
                                    )
                            );
                        }

                        $annotation = new CurrentAnnotation($target);
                    }
                } elseif ($annotation->ignored) {
                    $token->value = '';
                    $expression = Expression::$DOCBLOCK;
                }
            } else {
                $token->value .= $character;
            }
        }

        if (!($expression instanceof Docblock)) {
            throw new \ReflectionException(
                    'Annotation parser finished in wrong state for annotation '
                    . $target . '@' . $annotation->name
                    . ', annotation probably closed incorrectly, last state was '
                    . get_class($expression));
        }

        return $annotations;
    }
}
