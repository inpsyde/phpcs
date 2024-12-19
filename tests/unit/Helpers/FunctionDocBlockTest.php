<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests\Helpers;

use SydeCS\Syde\Helpers\FunctionDocBlock;
use SydeCS\Syde\Tests\TestCase;

class FunctionDocBlockTest extends TestCase
{
    /**
     * @test
     */
    public function testAllTags(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    /**
     * @param ?string $foo
     * @param bool $bool
     * @return string
     */
    function something(string $foo = null, bool $bool): string {
        /**
         * @param string $foo
         * @return string
         * @custom Hello World
         * @wp-hook
         */
        $foo = static function () {
            return '';
        };
        
        return '';
    }
}
PHP;

        $file = $this->factoryFile($php);

        $function1 = (int) $file->findNext(T_FUNCTION, 1);
        $function2 = (int) $file->findNext(T_CLOSURE, $function1 + 1);

        $tags1 = FunctionDocBlock::allTags($file, $function1);
        $tags2 = FunctionDocBlock::allTags($file, $function2);

        static::assertSame(
            [
                '@param' => ['?string $foo', 'bool $bool'],
                '@return' => ['string'],
            ],
            $tags1,
        );

        static::assertSame(
            [
                '@param' => ['string $foo'],
                '@return' => ['string'],
                '@custom' => ['Hello World'],
                '@wp-hook' => [''],
            ],
            $tags2,
        );
    }

    /**
     * @test
     */
    public function testTag(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    /**
     * @param string $foo
     * @return string
     * @customEmpty
     * @customFull  Hello There Foo
     *              next line
     */
    function one(string $foo): string {
        return $foo;
    }
    
    function two(string $foo): string {
        return $foo;
    }
    
    /**
     * @param string $foo
     * @return string
     * @customEmpty
     * @customFull Third
     * @customEmpty 
     * @customFull Third Again
     */
    function three(string $foo): string {
        return $foo;
    }
}
PHP;
        $file = $this->factoryFile($php);

        $function1 = (int) $file->findNext(T_FUNCTION, 1);
        $function2 = (int) $file->findNext(T_FUNCTION, $function1 + 1);
        $function3 = (int) $file->findNext(T_FUNCTION, $function2 + 1);

        static::assertSame('one', $file->getDeclarationName($function1));
        static::assertSame('two', $file->getDeclarationName($function2));
        static::assertSame('three', $file->getDeclarationName($function3));

        $customFull1 = FunctionDocBlock::tag('customFull', $file, $function1);
        $customEmpty1 = FunctionDocBlock::tag('customEmpty', $file, $function1);

        $customFull2 = FunctionDocBlock::tag('customFull', $file, $function2);
        $customEmpty2 = FunctionDocBlock::tag('customEmpty', $file, $function2);

        $customFull3 = FunctionDocBlock::tag('customFull', $file, $function3);
        $customEmpty3 = FunctionDocBlock::tag('customEmpty', $file, $function3);

        static::assertSame(["Hello There Foo\nnext line"], $customFull1);
        static::assertSame([''], $customEmpty1);

        static::assertSame([], $customFull2);
        static::assertSame([], $customEmpty2);

        static::assertSame(['Third', 'Third Again'], $customFull3);
        static::assertSame(['', ''], $customEmpty3);
    }

    /**
     * @test
     */
    public function testAllParamTypes(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    /**
     * @param string $foo
     * @param string|int|\SomeClass $bar
     * @return string
     */
    function something(string $foo, $bar): string {
        /**
         * @param ?string|int|\SomeClass $foo
         * @param string|null|int $bar
         * @return string
         * @custom Hello World
         * @wp-hook
         */
        $cb = static function ($foo, $bar) {
            return '';
        };
        
        return '';
    }
}
PHP;
        $file = $this->factoryFile($php);

        $function1 = (int) $file->findNext(T_FUNCTION, 1);
        $function2 = (int) $file->findNext(T_CLOSURE, $function1 + 1);

        $paramsOne = FunctionDocBlock::allParamTypes($file, $function1);
        $paramsTwo = FunctionDocBlock::allParamTypes($file, $function2);

        static::assertSame(
            [
                '$foo' => ['string'],
                '$bar' => ['\SomeClass', 'int', 'string'],
            ],
            $paramsOne,
        );

        static::assertSame(
            [
                '$foo' => ['\SomeClass', 'int', 'string', 'null'],
                '$bar' => ['int', 'string', 'null'],
            ],
            $paramsTwo,
        );
    }
}
