<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests\Helpers;

use SydeCS\Syde\Helpers\Functions;
use SydeCS\Syde\Tests\TestCase;

class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function testLooksLikeFunctionCall(): void
    {
        $php = <<<'PHP'
<?php
function x(): string {
    return ('foo + bar');
}
class Test {
    function x(): string {
        // one: `x()`
        return x();
    }

    function y(): callable {
        return function () {
            // two: `sprintf()`
            return sprintf /* comment is valid before parenthesis */(
                'foo %s bar',
                '(+)'
            );
        };
    }
}

('foo + bar');
// three: `x()`
echo (new Test())->x();
// four: `y()`
$y = (new Test())->y();
// five: `$y()`
$y();

// six: `require`
require('foo.php');

PHP;

        $file = $this->factoryFile($php);

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $functionCallContents = [];
        foreach ($tokens as $pos => $token) {
            if (Functions::looksLikeFunctionCall($file, $pos)) {
                $functionCallContents[] = $token['content'];
            }
        }

        static::assertSame(['x', 'sprintf', 'x', 'y', '$y', 'require'], $functionCallContents);
    }

    /**
     * @test
     */
    public function testIsUntypedPsrMethodWithClass(): void
    {
        $php = <<<'PHP'
<?php
use \Psr\Container\ContainerInterface;

class Container implements Foo, Bar\X, ContainerInterface {

    private $data = [];

    public function get($id)
    {
        return $this->data[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->data[$id]);
    }
}
PHP;
        $file = $this->factoryFile($php);

        $getFunc = (int) $file->findNext(T_FUNCTION, 1);
        $hasFunc = (int) $file->findNext(T_FUNCTION, $getFunc + 2);

        static::assertSame('get', $file->getDeclarationName($getFunc));
        static::assertSame('has', $file->getDeclarationName($hasFunc));

        static::assertTrue(Functions::isPsrMethod($file, $getFunc));
        static::assertTrue(Functions::isPsrMethod($file, $hasFunc));
    }

    /**
     * @test
     */
    public function testIsUntypedPsrMethodWithAnonClass(): void
    {
        $php = <<<'PHP'
<?php
namespace Test;

use Psr\Container\ContainerInterface as PsrContainer;

$x = new class implements Foo, PsrContainer, Bar {

    private $data = [];

    public function get($id)
    {
        return $this->data[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->data[$id]);
    }
};
PHP;
        $file = $this->factoryFile($php);

        $getFunc = (int) $file->findNext(T_FUNCTION, 1);
        $hasFunc = (int) $file->findNext(T_FUNCTION, $getFunc + 1);

        static::assertSame('get', $file->getDeclarationName($getFunc));
        static::assertSame('has', $file->getDeclarationName($hasFunc));

        static::assertTrue(Functions::isPsrMethod($file, $getFunc));
        static::assertTrue(Functions::isPsrMethod($file, $hasFunc));
    }
}
