<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests\Helpers;

use PHP_CodeSniffer\Util\Tokens;
use SydeCS\Syde\Helpers\Misc;
use SydeCS\Syde\Tests\TestCase;

class MiscTest extends TestCase
{
    /**
     * @return list<array{string, string}>
     */
    public static function provideMinVersions(): array
    {
        return [
            ['5.6', '7.4'],
            ['7', '7.4'],
            ['7.2.3', '7.4'],
            ['8', '8.0'],
            ['8.0', '8.0'],
            ['8.1', '8.1'],
            ['8.2.3', '8.2'],
        ];
    }

    /**
     * @test
     * @dataProvider provideMinVersions
     * @runInSeparateProcess
     */
    public function testMinPhpTestVersion(string $input, string $expected): void
    {
        $this->factoryFile('<?php ', $input);

        static::assertSame($expected, Misc::minPhpTestVersion());
    }

    /**
     * @test
     */
    public function tokensSubsetToString(): void
    {
        $php = <<<'PHP'
<?php
function x(): string {
    return ('foo + bar');
}
PHP;
        $file = $this->factoryFile($php);

        $tokens = $file->getTokens();

        $start = (int) $file->findNext(T_FUNCTION, 1);
        $end = count($tokens) - 1;

        $expected = <<<'PHP'
function x(): string {
    return ('foo + bar');
}
PHP;

        $actual = Misc::tokensSubsetToString($start, $end, $file, [], true);

        static::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function tokensSubsetToStringExclude(): void
    {
        $php = <<<'PHP'
<?php
function x(): string {
    /** foo */
    return ('foo + bar');
}
PHP;
        $file = $this->factoryFile($php);

        $tokens = $file->getTokens();

        $start = (int) $file->findNext(T_FUNCTION, 1);
        $end = count($tokens) - 1;

        $exclude = array_merge(array_keys(Tokens::$emptyTokens), [T_RETURN]);

        $expected = <<<'PHP'
x():string{('foo + bar');}
PHP;

        $actual = Misc::tokensSubsetToString($start + 1, $end, $file, $exclude, true);

        static::assertSame($expected, $actual);
    }
}
