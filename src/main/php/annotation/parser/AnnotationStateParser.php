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
use stubbles\reflect\annotation\parser\state\{
    AnnotationState,
    CurrentAnnotation,
    InAnnotation,
    AnnotationForArgument,
    AnnotationName,
    AnnotationType,
    Docblock,
    EnclosedParamValue,
    Parameters,
    ParamName,
    ParamValue
};
/**
 * Parser to parse Java-Style annotations.
 *
 * @internal
 */
class AnnotationStateParser implements AnnotationParser
{
    /**
     * possible states
     *
     * @type  \stubbles\reflect\annotation\parser\state\AnnotationState[]
     */
    private $states;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->states = [
                AnnotationState::DOCBLOCK                     => new Docblock(),
                AnnotationState::ANNOTATION                   => new InAnnotation(),
                AnnotationState::ANNOTATION_NAME              => new AnnotationName(),
                AnnotationState::ANNOTATION_TYPE              => new AnnotationType(),
                AnnotationState::ARGUMENT                     => new AnnotationForArgument(),
                AnnotationState::PARAM_NAME                   => new ParamName(),
                AnnotationState::PARAM_VALUE                  => new ParamValue(),
                AnnotationState::PARAM_VALUE_IN_SINGLE_QUOTES => new EnclosedParamValue("'"),
                AnnotationState::PARAM_VALUE_IN_DOUBLE_QUOTES => new EnclosedParamValue('"')
        ];
    }

    /**
     * parse a docblock and return all annotations found
     *
     * @param   string  $docComment
     * @param   string  $target
     * @return  \stubbles\reflect\annotation\Annotations[]
     * @throws  \ReflectionException
     */
    public static function parseFrom(string $docComment, string $target): array
    {
        static $self = null;
        if (null === $self) {
            $self = new self();
        }

        return $self->parse($docComment, $target);
    }

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
        $annotations  = [$target => new Annotations($target)];
        $annotation   = new CurrentAnnotation($target);
        $currentState = $this->states[AnnotationState::DOCBLOCK];
        // substract last two characters which close the doc comment from length
        $len  = strlen($docComment) - 2;
        $word = new \stdClass();
        $word->content = '';
        for ($i = 6; $i < $len; $i++) {
            $currentToken = $docComment{$i};
            if (isset($currentState->signalTokens[$currentToken])) {
                if ($currentState->process($word, $currentToken, $annotation)) {
                    $word->content      = '';
                    $currentState = $this->states[$currentState->signalTokens[$currentToken]];
                    if ($currentState instanceof Docblock) {
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
                    $word->content = '';
                    $currentState  = $this->states[AnnotationState::DOCBLOCK];
                }
            } else {
                $word->content .= $currentToken;
            }
        }

        if (!($currentState instanceof Docblock)) {
            throw new \ReflectionException(
                    'Annotation parser finished in wrong state for annotation '
                    . $target . '@' . $annotation->name
                    . ', annotation probably closed incorrectly, last state was '
                    . get_class($currentState));
        }

        return $annotations;
    }
}
