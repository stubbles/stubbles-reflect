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
    private $states             = [];
    /**
     * the current state
     *
     * @type  \stubbles\reflect\annotation\parser\state\AnnotationParserState
     */
    private $currentState       = null;
    /**
     * all parsed annotations
     *
     * @type  array
     */
    private $annotations        = [];

    /**
     * constructor
     */
    public function __construct()
    {
        $this->states = [
                AnnotationState::DOCBLOCK                     => new Docblock(),
                AnnotationState::ANNOTATION                   => new InAnnotation(),
                AnnotationState::ANNOTATION_NAME              => new AnnotationName($this),
                AnnotationState::ANNOTATION_TYPE              => new AnnotationType(),
                AnnotationState::ARGUMENT                     => new AnnotationForArgument(),
                AnnotationState::PARAM_NAME                   => new ParamName(),
                AnnotationState::PARAM_VALUE                  => new ParamValue(),
                AnnotationState::PARAM_VALUE_IN_SINGLE_QUOTES => new EnclosedParamValue("'"),
                AnnotationState::PARAM_VALUE_IN_DOUBLE_QUOTES => new EnclosedParamValue('"')
        ];
        $this->currentState = $this->states[AnnotationState::DOCBLOCK];
    }

    /**
     * change the current state
     *
     * @param   int     $state
     */
    public function changeState(int $state)
    {
        $this->currentState = $this->states[$state];
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
        $this->annotations = [$target => new Annotations($target)];
        $currentAnnotation = new CurrentAnnotation($target);
        $len  = strlen($docComment);
        $word = new \stdClass();
        $word->content = '';
        for ($i = 6; $i < $len; $i++) {
            $currentToken = $docComment{$i};
            if (isset($this->currentState->signalTokens[$currentToken])) {
                if ($this->currentState->process($word, $currentToken, $currentAnnotation)) {
                    $word->content      = '';
                    $this->currentState = $this->states[$this->currentState->signalTokens[$currentToken]];
                    if ($this->currentState instanceof Docblock) {
                        $this->finalize($currentAnnotation);
                        $currentAnnotation = new CurrentAnnotation($target);
                    }
                }
            } else {
                $word->content .= $currentToken;
            }
        }

        if (!($this->currentState instanceof Docblock)) {
            throw new \ReflectionException(
                    'Annotation parser finished in wrong state for annotation '
                    . $target . '@' . $currentAnnotation->name
                    . ', annotation probably closed incorrectly, last state was '
                    . get_class($this->currentState));
        }

        return $this->annotations;
    }

    /**
     * finalizes the current annotation
     */
    private function finalize(CurrentAnnotation $annotation)
    {
        if (null === $annotation->name) {
            return;
        }

        if (!isset($this->annotations[$annotation->target])) {
            $this->annotations[$annotation->target] = new Annotations($annotation->target);
        }

        $this->annotations[$annotation->target]->add(
                new Annotation(
                        $annotation->type,
                        $annotation->target,
                        $annotation->params,
                        $annotation->name
                )
        );
    }
}
