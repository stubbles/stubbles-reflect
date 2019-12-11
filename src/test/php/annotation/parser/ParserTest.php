<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation\parser;
use PHPUnit\Framework\TestCase;
use stubbles\reflect\annotation\Annotation;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\fail;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\reflect\annotation\Parser.
 *
 * @group  reflect
 * @group  annotation
 * @group  parser
 */
class ParserTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  \stubbles\reflect\annotation\parser\Parser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * @param   string               $name
     * @param   array<string,mixed>  $values
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
        $docComment = $clazz->getDocComment();
        if (false === $docComment) {
            fail('Could not retriece doc comment');
        }

        return $this->parser->parse($docComment, MyTestClass::class)[MyTestClass::class]->named($type);
    }

    /**
     * @test
     */
    public function parsesAnnotationWithoutValues(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('Foo'),
                equals($this->expectedClassAnnotation('Foo'))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithoutValuesButParentheses(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('FooWithBrackets'),
                equals($this->expectedClassAnnotation('FooWithBrackets'))
        );
    }

    /**
     * @test
     */
    public function parsesCastedAnnotation(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('Bar'),
                equals($this->expectedClassAnnotation('TomTom', [], 'Bar'))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithSingleValue(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('MyAnnotation'),
                equals($this->expectedClassAnnotation('MyAnnotation', ['foo' => 'bar']))
        );
    }

    /**
     * @test
     */
    public function parsesAnnotationWithValues(): void
    {
        assertThat(
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
    public function parsesAnnotationWithValueContainingSignalCharacters(): void
    {
        assertThat(
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
    public function parsesAnnotationWithConstantAsValue(): void
    {
        assertThat(
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
    public function parsesAnnotationWithStringContainingEscapedCharacters(): void
    {
        assertThat(
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
    public function parsesAnnotationSpanningMultipleLine(): void
    {
        assertThat(
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
    public function parsesAnnotationWithClassAsValue(): void
    {
        assertThat(
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
    public function tabsAreNoProblemForParsing(): void
    {
        $comment = "/**\n\t * This is a test class that has many annotations.\n\t *\n\t * @Foo\n\t */";
        assertThat(
                iterator_to_array(
                        $this->parser->parse($comment, 'tabs')['tabs']
                                ->all()
                ),
                equals([new Annotation('Foo', 'tabs')])
        );
    }

    /**
     * @param   string               $name
     * @param   array<string,mixed>  $values
     * @param   string               $type
     * @return  \stubbles\reflect\annotation\Annotation[]
     */
    private function expectedParameterAnnotation(string $name, array $values = [], string $type = null): array
    {
        return [new Annotation($name, MyTestClass2::class . '::foo()#bar', $values, $type)];
    }

    /**
     * @param   string  $type
     * @return  \stubbles\reflect\annotation\Annotation[]
     */
    private function parseMyTestClass2Annotation(string $type): array
    {
        $method = new \ReflectionMethod(MyTestClass2::class, 'foo');
        $docComment = $method->getDocComment();
        if (false === $docComment) {
            fail('Could not retrieve doc comment');
        }

        return $this->parser->parse(
                $docComment,
                MyTestClass2::class . '::foo()'
        )[MyTestClass2::class . '::foo()#bar']->named($type);
    }

    /**
     * @test
     */
    public function parsesArgumentAnnotationFromMethodDocComment(): void
    {
        assertThat(
                $this->parseMyTestClass2Annotation('ForArgument1'),
                equals($this->expectedParameterAnnotation('ForArgument1'))
        );
    }

    /**
     * @test
     */
    public function parsesArgumentAnnotationWithValuesFromMethodDocComment(): void
    {
        assertThat(
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
    public function parsesCastedArgumentAnnotationFromMethodDocComment(): void
    {
        assertThat(
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
    public function parsesCastedArgumentAnnotationWithValuesFromMethodDocComment(): void
    {
        assertThat(
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
    public function parsesCastedArgumentAnnotationDifferentOrderFromMethodDocComment(): void
    {
        assertThat(
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
    public function parsesCastedArgumentAnnotationDifferentOrderWithValuesFromMethodDocComment(): void
    {
        assertThat(
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
    public function parseIncompleteDocblockThrowsReflectionException(): void
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
    public function missingEqualSignThrowsReflectionException(): void
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
    public function paramNameStartingWithEqualSignThrowsReflectionException(): void
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
    public function paramNameWithInvalidCharacterThrowsReflectionException(): void
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
    public function moreThanOneSingleValueThrowsReflectionException(): void
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
    public function registerSingleAnnotationAfterParamValueThrowsReflectionException(): void
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
    public function stringWithDoubleQuotesInsideSingleQuotes(): void
    {
        $annotations = $this->parser->parse('/**
     * a method with an annotation for its parameter
     *
     * @Foo(name=\'dum "di" dam\')
     */',
                'Bar::someMethod()');
        assertThat(
                $annotations['Bar::someMethod()']->firstNamed('Foo')->getName(),
                equals('dum "di" dam')
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function canNotUseEmptyParameterNamesInParamAnnotation(): void
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
    public function canNotUseInvalidParameterNamesInParamAnnotation(): void
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
    public function canNotUseEmptyAnnotationType(): void
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
    public function canNotUseInvalidAnnotationType(): void
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
    public function annotationSignWithoutNameThrowsReflectionException(): void
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
    public function annotationNameWithInvalidCharactersThrowsReflectionException(): void
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
    public function canParseMultilineIndentedAnnotation(): void
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
        assertThat(
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
