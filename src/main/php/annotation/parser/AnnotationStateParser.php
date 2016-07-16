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
     * the name of the current annotation
     *
     * @type  string
     */
    private $currentAnnotation  = null;
    /**
     * the name of the current annotation parameter
     *
     * @type  string
     */
    private $currentParam       = null;
    /**
     * current target
     *
     * @var  string
     */
    private $currentTarget;
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
                AnnotationState::ANNOTATION_TYPE              => new AnnotationType($this),
                AnnotationState::ARGUMENT                     => new AnnotationForArgument($this),
                AnnotationState::PARAM_NAME                   => new ParamName($this),
                AnnotationState::PARAM_VALUE                  => new ParamValue($this),
                AnnotationState::PARAM_VALUE_IN_SINGLE_QUOTES => new EnclosedParamValue($this, "'"),
                AnnotationState::PARAM_VALUE_IN_DOUBLE_QUOTES => new EnclosedParamValue($this, '"')
        ];
        $this->currentState = $this->states[AnnotationState::DOCBLOCK];
    }

    /**
     * change the current state
     *
     * @param   int     $state
     * @throws  \ReflectionException
     */
    public function changeState(int $state)
    {
        if (!isset($this->states[$state])) {
            throw new \ReflectionException('Unknown state ' . $state);
        }

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
        $this->currentTarget = $target;
        $this->annotations   = [$target => new Annotations($target)];
        $len  = strlen($docComment);
        $word = new \stdClass();
        $word->content = '';
        for ($i = 6; $i < $len; $i++) {
            $currentToken = $docComment{$i};
            if (isset($this->currentState->signalTokens[$currentToken])) {
                if ($this->currentState->process($word, $currentToken)) {
                    $word->content      = '';
                    $this->currentState = $this->states[$this->currentState->signalTokens[$currentToken]];
                }
            } else {
                $word->content .= $currentToken;
            }
        }

        if (!($this->currentState instanceof Docblock)) {
            throw new \ReflectionException(
                    'Annotation parser finished in wrong state for annotation '
                    . $target
                    . (isset($this->currentAnnotation['name']) ? '@' . $this->currentAnnotation['name'] : '')
                    . ', annotation probably closed incorrectly, last state was '
                    . get_class($this->currentState));
        }

        $this->finalize();
        return $this->annotations;
    }

    /**
     * finalizes the current annotation
     */
    private function finalize()
    {
        if (null === $this->currentAnnotation) {
            return;
        }

        $target = $this->currentAnnotation['target'] ?? $this->currentTarget;
        if (!isset($this->annotations[$target])) {
            $this->annotations[$target] = new Annotations($target);
        }

        $this->annotations[$target]->add(
                new Annotation(
                        $this->currentAnnotation['type'],
                        $target,
                        $this->currentAnnotation['params'],
                        $this->currentAnnotation['name']
                )
        );

        $this->currentAnnotation = null;
    }

    /**
     * register a new annotation
     *
     * @param  string  $name
     */
    public function registerAnnotation(string $name)
    {
        $this->finalize();
        $this->currentAnnotation = ['name'   => $name,
                                    'type'   => $name,
                                    'params' => []
                                   ];
    }

    /**
     * register a new annotation param
     *
     * @param  string  $name
     */
    public function registerAnnotationParam(string $name)
    {
        $this->currentParam = trim($name);
    }

    /**
     * register single annotation param
     *
     * @param   string  $value  the value of the param
     * @throws  \ReflectionException
     */
    public function registerSingleAnnotationParam(string $value)
    {
        if (count($this->currentAnnotation['params']) > 0) {
            throw new \ReflectionException('Error parsing annotation ' . $this->currentAnnotation['type']);
        }

        $this->currentAnnotation['params']['__value'] = $value;
    }

    /**
     * set the annoation param value for the current annotation
     *
     * @param  string  $value  the value of the param
     */
    public function setAnnotationParamValue(string $value)
    {
        $this->currentAnnotation['params'][$this->currentParam] = $value;
    }

    /**
     * set the type of the current annotation
     *
     * @param  string  $type  type of the annotation
     */
    public function setAnnotationType(string $type)
    {
        $this->currentAnnotation['type'] = $type;
    }

    /**
     * sets the argument for which the annotation is declared
     *
     * @param  string  $parameterName  name of the argument
     */
    public function markAsParameterAnnotation(string $parameterName)
    {
        $this->currentAnnotation['target'] = $this->currentTarget . '#' . $parameterName;
    }
}
