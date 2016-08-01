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
use stubbles\reflect\annotation\Annotation;

use function bovigo\assert\assert;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * This is a test class that has many annotations.
 *
 * @Foo
 * @FooWithBrackets ()
 * @Bar[TomTom]
 * @MyAnnotation(foo='bar')
 * @TwoParams(foo='bar', test=42)
 * @InvalidChars(foo='ba@r=,')
 * @Constant(foo=stubbles\reflect\annotation\parser\MyTestClass::TEST_CONSTANT)
 * @WithEscaped(foo='This string contains \' and \, which is possible using escaping...')
 * @Multiline(one=1,
 *            two=2)
 * @Class(stubbles\reflect\annotation\parser\MyTestClass.class)
 */
class MyTestClass
{
    const TEST_CONSTANT = 'baz';
}
class MyTestClass2
{
    /**
     * a method with an annotation for its parameter
     *
     * @param  string  $bar
     * @ForArgument1{bar}
     * @ForArgument2{bar}(key='value')
     * @MoreArgument1{bar}[Casted]
     * @MoreArgument2{bar}[Casted](key='value')
     * @MoreArgument3[CastedAround]{bar}
     * @MoreArgument4[CastedAround]{bar}(key='value')
     * @another
     */
    public function foo(string $bar) { }
}
/**
 * Test for stubbles\reflect\annotation\Parser.
 *
 * @group  reflect
 * @group  annotation
 * @group  parser
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\reflect\annotation\parser\Parser
     */
    private $parser;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->parser = new Parser();
    }

    /**
     * @param   string  $name
     * @param   array   $values
     * @return  \stubbles\reflect\annotation\Annotation[]
     */
    private function expectedClassAnnotation(string $name, array $values = [], string $type = null): array
    {
        return [new Annotation($name, MyTestClass::class, $values, $type)];
    }

    /**
     * @return  \stubbles\reflect\annotation\Annotation[]
     */
    private function parseMyTestClassAnnotation(string $type): array
    {
        $clazz = new \ReflectionClass(MyTestClass::class);
        return $this->parser->parse(
                $clazz->getDocComment(),
                MyTestClass::class
        )[MyTestClass::class]->named($type);
    }

    /**
     * @test
     */
    public function parsesAnnotationWithoutValues()
    {
        assert(
                $this->parseMyTestClassAnnotation('Foo'),
                equals($this->expectedClassAnnotation('Foo'))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithoutValuesButParentheses()
    {
        assert(
                $this->parseMyTestClassAnnotation('FooWithBrackets'),
                equals($this->expectedClassAnnotation('FooWithBrackets'))
        );
    }

    /**
     * @test
     */
    public function parsesCastedAnnotation()
    {
        assert(
                $this->parseMyTestClassAnnotation('Bar'),
                equals($this->expectedClassAnnotation('TomTom', [], 'Bar'))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithSingleValue()
    {
        assert(
                $this->parseMyTestClassAnnotation('MyAnnotation'),
                equals($this->expectedClassAnnotation('MyAnnotation', ['foo' => 'bar']))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithValues()
    {
        assert(
                $this->parseMyTestClassAnnotation('TwoParams'),
                equals($this->expectedClassAnnotation(
                        'TwoParams',
                        ['foo' => 'bar', 'test' => 42]
                ))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithValueContainingSignalCharacters()
    {
        assert(
                $this->parseMyTestClassAnnotation('InvalidChars'),
                equals($this->expectedClassAnnotation(
                        'InvalidChars',
                        ['foo' => 'ba@r=,']
                ))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithConstantAsValue()
    {
        assert(
                $this->parseMyTestClassAnnotation('Constant'),
                equals($this->expectedClassAnnotation(
                        'Constant',
                        ['foo' => MyTestClass::class . '::TEST_CONSTANT']
                ))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithStringContainingEscapedCharacters()
    {
        assert(
                $this->parseMyTestClassAnnotation('WithEscaped'),
                equals($this->expectedClassAnnotation(
                        'WithEscaped',
                        ['foo' => "This string contains ' and \, which is possible using escaping..."]
                ))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationSpanningMultipleLine()
    {
        assert(
                $this->parseMyTestClassAnnotation('Multiline'),
                equals($this->expectedClassAnnotation(
                        'Multiline',
                        ['one' => 1, 'two' => 2]
                ))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithClassAsValue()
    {
        assert(
                $this->parseMyTestClassAnnotation('Class'),
                equals($this->expectedClassAnnotation(
                        'Class',
                        ['__value' => MyTestClass::class . '.class']
                ))
        );
    }

    /**
     * @test
     */
    public function tabsAreNoProblemForParsing()
    {
        $comment = "/**\n\t * This is a test class that has many annotations.\n\t *\n\t * @Foo\n\t */";
        assert(
                iterator_to_array(
                        $this->parser->parse($comment, 'tabs')['tabs']
                                ->all()
                ),
                equals([new Annotation('Foo', 'tabs')])
        );
    }

    /**
     * @param   string  $name
     * @param   array   $values
     * @return  \stubbles\reflect\annotation\Annotation[]
     */
    private function expectedParameterAnnotation($name, array $values = [], $type = null)
    {
        return [new Annotation($name, MyTestClass2::class . '::foo()#bar', $values, $type)];
    }

    /**
     * @return  \stubbles\reflect\annotation\Annotation[]
     */
    private function parseMyTestClass2Annotation($type)
    {
        $method = new \ReflectionMethod(MyTestClass2::class, 'foo');
        return $this->parser->parse(
                $method->getDocComment(),
                MyTestClass2::class . '::foo()'
        )[MyTestClass2::class . '::foo()#bar']->named($type);
    }

    /**
     * @test
     */
    public function parsesArgumentAnnotationFromMethodDocComment()
    {
        assert(
                $this->parseMyTestClass2Annotation('ForArgument1'),
                equals($this->expectedParameterAnnotation('ForArgument1'))
        );
    }

    /**
     * @test
     */
    public function parsesArgumentAnnotationWithValuesFromMethodDocComment()
    {
        assert(
                $this->parseMyTestClass2Annotation('ForArgument2'),
                equals($this->expectedParameterAnnotation(
                        'ForArgument2',
                        ['key' => 'value']
                ))
        );
    }

    /**
     * @test
     */
    public function parsesCastedArgumentAnnotationFromMethodDocComment()
    {
        assert(
                $this->parseMyTestClass2Annotation('MoreArgument1'),
                equals($this->expectedParameterAnnotation(
                        'Casted',
                        [],
                        'MoreArgument1'
                ))
        );
    }

    /**
     * @test
     */
    public function parsesCastedArgumentAnnotationWithValuesFromMethodDocComment()
    {
        assert(
                $this->parseMyTestClass2Annotation('MoreArgument2'),
                equals($this->expectedParameterAnnotation(
                        'Casted',
                        ['key' => 'value'],
                        'MoreArgument2'
                ))
        );
    }

    /**
     * @test
     */
    public function parsesCastedArgumentAnnotationDifferentOrderFromMethodDocComment()
    {
        assert(
                $this->parseMyTestClass2Annotation('MoreArgument3'),
                equals($this->expectedParameterAnnotation(
                        'CastedAround',
                        [],
                        'MoreArgument3'
                ))
        );
    }

    /**
     * @test
     */
    public function parsesCastedArgumentAnnotationDifferentOrderWithValuesFromMethodDocComment()
    {
        assert(
                $this->parseMyTestClass2Annotation('MoreArgument4'),
                equals($this->expectedParameterAnnotation(
                        'CastedAround',
                        ['key' => 'value'],
                        'MoreArgument4'
                ))
        );
    }

    /**
     * @test
     */
    public function parseIncompleteDocblockThrowsReflectionException()
    {
        expect(function() {
                $this->parser->parse('/**
             * a method with an annotation for its parameter
             *
             * @ForArgument1{bar}',
                        'Bar::someMethod()');
        })
        ->throws(\ReflectionException::class);
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function missingEqualSignThrowsReflectionException()
    {
        expect(function() {
                $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo(yo\'dum "di" dam\')
     */',
                'Bar::someMethod()');
        })
                ->throws(\ReflectionException::class)
                ->withMessage(
                        'Annotation parameter "yo" for Bar::someMethod()@Foo may'
                        . ' contain letters, underscores and numbers, but contains '
                        . '\'. Probably an equal sign is missing.'
                );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function paramNameStartingWithEqualSignThrowsReflectionException()
    {
        expect(function() {
                $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo(=\'dum "di" dam\')
     */',
                'Bar::someMethod()');
        })
                ->throws(\ReflectionException::class)
                ->withMessage(
                        'Annotation parameter for Bar::someMethod()@Foo has to'
                        . ' start with a letter or underscore, but starts with "="'
                );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function paramNameWithInvalidCharacterThrowsReflectionException()
    {
        expect(function() {
                $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo(1=\'dum "di" dam\')
     */',
                'Bar::someMethod()');
        })
                ->throws(\ReflectionException::class)
                ->withMessage(
                        'Annotation parameter for Bar::someMethod()@Foo must start'
                        . ' with a letter or underscore and contain letters,'
                        . ' underscores and numbers, but contains an invalid'
                        . ' character: 1'
                );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function moreThanOneSingleValueThrowsReflectionException()
    {
        expect(function() {
            $this->parser->parse('/**
         * a method with an annotation for its parameter
         *
         * @Foo(\'dum "di" dam\', true)
         */',
                    'Bar::someMethod()');
        })
                ->throws(\ReflectionException::class)
                ->withMessage(
                        'Error in annotation Bar::someMethod()@Foo(dum "di" dam),'
                        . ' contains two values without name.'
                );
    }

    /**
     * @test
     */
    public function registerSingleAnnotationAfterParamValueThrowsReflectionException()
    {
        expect(function() {
            $this->parser->parse('/**
         * a method with an annotation for its parameter
         *
         * @Foo(name=\'dum "di" dam\', true)
         */',
                    'Bar::someMethod()');
        })
                ->throws(\ReflectionException::class)
                ->withMessage(
                        'Error in annotation Bar::someMethod()@Foo(name=dum "di" dam),'
                        . ' contains value "true" without a name after named values'
                );
    }

    /**
     * @test
     * @since  5.5.1
     */
    public function stringWithDoubleQuotesInsideSingleQuotes()
    {
        $annotations = $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo(name=\'dum "di" dam\')
     */',
                'Bar::someMethod()');
        assert(
                $annotations['Bar::someMethod()']->firstNamed('Foo')->getName(),
                equals('dum "di" dam')
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function canNotUseEmptyParameterNamesInParamAnnotation()
    {
        expect(function() {
            $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo{}
     */',
                'Bar::someMethod()');
        })
            ->throws(\ReflectionException::class)
            ->withMessage('Argument name for annotation Bar::someMethod()@Foo is empty.');
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function canNotUseInvalidParameterNamesInParamAnnotation()
    {
        expect(function() {
            $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo{1}
     */',
                'Bar::someMethod()');
        })
            ->throws(\ReflectionException::class)
            ->withMessage(
                    'Argument name for annotation Bar::someMethod()@Foo is not a'
                    . ' valid parameter name: 1'
            );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function canNotUseEmptyAnnotationType()
    {
        expect(function() {
            $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo[]
     */',
                'Bar::someMethod()');
        })
            ->throws(\ReflectionException::class)
            ->withMessage('Annotation type for Bar::someMethod()@Foo can not be empty.');
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function canNotUseInvalidAnnotationType()
    {
        expect(function() {
            $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo[1]
     */',
                'Bar::someMethod()');
        })
            ->throws(\ReflectionException::class)
            ->withMessage(
                    'Annotation type for Bar::someMethod()@Foo must start with a'
                    . ' letter or underscore and may contain letters, underscores'
                    . ' and numbers, but contains an invalid character: 1'
            );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function annotationSignWithoutNameThrowsReflectionException()
    {
        expect(function() {
            $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @
     */',
                'Bar::someMethod()');
        })
            ->throws(\ReflectionException::class)
            ->withMessage('Annotation name for Bar::someMethod()@ can not be empty');
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function annotationNameWithInvalidCharactersThrowsReflectionException()
    {
        expect(function() {
            $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @1
     */',
                'Bar::someMethod()');
        })
            ->throws(\ReflectionException::class)
            ->withMessage(
                    'Annotation name for Bar::someMethod()@ must start with a'
                    . ' letter or underscore and may contain letters, underscores'
                    . ' and numbers, but contains an invalid character: @1'
            );
    }

    /**
     * @test
     * @group  multiline
     * @since  8.0.2
     */
    public function canParseMultilineIndentedAnnotation()
    {
        $annotations = $this->parser->parse("/**
 * Helper class for the test.
 *
 * @RssFeedItem(titleMethod='getHeadline',
 *              linkMethod='getUrl',
 *              descriptionMethod='getTeaser',
 *              authorMethod='getCreator',
 *              categoriesMethod='getTags',
 *              getCommentsUrlMethod='getRemarks',
 *              enclosuresMethod='getImages',
 *              guidMethod='getId',
 *              isPermaLinkMethod='isPermanent',
 *              pubDateMethod='getDate',
 *              sourcesMethod='getOrigin',
 *              contentMethod='getText'
 * )
 */",
                'Bar::someMethod()');
        assert(
                $annotations['Bar::someMethod()']->firstNamed('RssFeedItem'),
                equals(new Annotation('RssFeedItem', 'Bar::someMethod()', [
                        'titleMethod'          => 'getHeadline',
                        'linkMethod'           => 'getUrl',
                        'descriptionMethod'    => 'getTeaser',
                        'authorMethod'         => 'getCreator',
                        'categoriesMethod'     => 'getTags',
                        'getCommentsUrlMethod' => 'getRemarks',
                        'enclosuresMethod'     => 'getImages',
                        'guidMethod'           => 'getId',
                        'isPermaLinkMethod'    => 'isPermanent',
                        'pubDateMethod'        => 'getDate',
                        'sourcesMethod'        => 'getOrigin',
                        'contentMethod'        => 'getText'
                ]))
        );
    }
}
