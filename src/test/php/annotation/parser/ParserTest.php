<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation\parser;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\reflect\annotation\Annotation;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\fail;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\reflect\annotation\Parser.
 */
#[Group('reflect')]
#[Group('annotation')]
#[Group('parser')]
class ParserTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * @param   array<string,mixed>  $values
     * @return  Annotation[]
     */
    private function expectedClassAnnotation(string $name, array $values = [], ?string $type = null): array
    {
        return [new Annotation($name, MyTestClass::class, $values, $type)];
    }

    /**
     * @return  Annotation[]
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

    #[Test]
    public function parsesAnnotationWithoutValues(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('Foo'),
                equals($this->expectedClassAnnotation('Foo'))
        );
    }

    #[Test]
    public function parsesAnnotationWithoutValuesButParentheses(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('FooWithBrackets'),
                equals($this->expectedClassAnnotation('FooWithBrackets'))
        );
    }

    #[Test]
    public function parsesCastedAnnotation(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('Bar'),
                equals($this->expectedClassAnnotation('TomTom', [], 'Bar'))
        );
    }

    #[Test]
    public function parsesAnnotationWithSingleValue(): void
    {
        assertThat(
                $this->parseMyTestClassAnnotation('MyAnnotation'),
                equals($this->expectedClassAnnotation('MyAnnotation', ['foo' => 'bar']))
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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
     * @param   array<string,mixed>  $values
     * @return  Annotation[]
     */
    private function expectedParameterAnnotation(string $name, array $values = [], ?string $type = null): array
    {
        return [new Annotation($name, MyTestClass2::class . '::foo()#bar', $values, $type)];
    }

    /**
     * @return  Annotation[]
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

    #[Test]
    public function parsesArgumentAnnotationFromMethodDocComment(): void
    {
        assertThat(
                $this->parseMyTestClass2Annotation('ForArgument1'),
                equals($this->expectedParameterAnnotation('ForArgument1'))
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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

    #[Test]
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
     * @since  5.5.1
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.0
     */
    #[Test]
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
     * @since  8.0.2
     */
    #[Test]
    #[Group('multiline')]
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
