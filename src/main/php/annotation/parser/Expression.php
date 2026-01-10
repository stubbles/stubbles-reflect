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
 * Represents an expression that can be encountered during parsing.
 *
 * @internal
 * @deprecated since 11.1.0, will be removed with 12.0.0, use attributes instead
 */
abstract class Expression
{
    public static Expression $DOCBLOCK;
    /**
     * map of characters which signal that this expressions ends and which expression follows
     *
     * @var  array<string,Expression>
     */
    public array $after = [];
    protected static Expression $ANNOTATION;
    protected static Expression $ANNOTATION_NAME;
    protected static Expression $ANNOTATION_TYPE;
    protected static Expression $PARAM_NAME;
    protected static Expression $PARAM_VALUE;
    protected static Expression $ARGUMENT;
    protected static Expression $PARAM_VALUE_IN_SINGLE_QUOTES;
    protected static Expression $PARAM_VALUE_IN_DOUBLE_QUOTES;

    /**
     * static initializing
     */
    public static function __static(): void
    {
        self::$DOCBLOCK                     = new Docblock();
        self::$ANNOTATION                   = new InAnnotation();
        self::$ANNOTATION_NAME              = new AnnotationName();
        self::$ANNOTATION_TYPE              = new AnnotationType();
        self::$ARGUMENT                     = new AnnotationForArgument();
        self::$PARAM_NAME                   = new ParamName();
        self::$PARAM_VALUE                  = new ParamValue();
        self::$PARAM_VALUE_IN_SINGLE_QUOTES = new EnclosedParamValue();
        self::$PARAM_VALUE_IN_DOUBLE_QUOTES = new EnclosedParamValue();
        self::$DOCBLOCK->init();
        self::$ANNOTATION->init();
        self::$ANNOTATION_NAME->init();
        self::$ANNOTATION_TYPE->init();
        self::$ARGUMENT->init();
        self::$PARAM_NAME->init();
        self::$PARAM_VALUE->init();
        self::$PARAM_VALUE_IN_SINGLE_QUOTES->init("'");
        self::$PARAM_VALUE_IN_DOUBLE_QUOTES->init('"');
    }

    /**
     * evaluates a token and the detected signal into the annotation
     *
     * @param   Token              $token       parsed token to be processed
     * @param   string             $signal      signal encountered by parser
     * @param   CurrentAnnotation  $annotation  currently parsed annotation
     * @return  bool
     */
     public function evaluate(Token $token, string $signal, CurrentAnnotation $annotation): bool
     {
         return true;
     }
}
Expression::__static();
